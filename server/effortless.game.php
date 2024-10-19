<?php
/**
 * effortless.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

$swdNamespaceAutoload = function ($class) {
  $classParts = explode('\\', $class);
  if (in_array($classParts[0], ['Effortless', 'WcLib'])) {
    if ($classParts[0] == 'Effortless') {
      array_shift($classParts);
    }
    $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
      throw new \feException('Cannot find file: ' . $file);
      var_dump('Cannot find file: ' . $file);
    }
  }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

// @phan-suppress-next-line PhanUndeclaredConstant
require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

// require_once 'modules/php/card_data.inc.php';
require_once 'modules/php/constants.inc.php';

// require_once 'modules/php/Models/Card.php';
// require_once 'modules/php/Models/Location.php';
// require_once 'modules/php/Models/Setting.php';

// // use Effortless\Managers\Board;

// use Effortless\Models\Player;

use WcLib\EventDispatcher;

abstract class TableBase extends Table
{
  /** @var EventDispatcher<int> */
  public $onEnteringState;
  /** @var EventDispatcher<int> */
  public $onLeavingState;

  private ?int $last_state_ = null;

  function __construct()
  {
    parent::__construct();

    /** @var EventDispatcher<int> */
    $this->onEnteringState = new EventDispatcher(null);
    /** @var EventDispatcher<int> */
    $this->onLeavingState = new EventDispatcher(null);
  }

  // This should be the first function called at the top of each `st*()` (state action) function.
  function triggerStateEvents(): void
  {
    if ($this->last_state_ !== null) {
      $this->onLeavingState->dispatch($this->last_state_);
    }

    $next_state = $this->getGameStateValue('bgaCurrentState');
    $this->last_state_ = $next_state;
    $this->onEnteringState->dispatch($next_state);
  }
}

class Effortless extends TableBase
{
  use Effortless\ActionDispatchTrait;
  use Effortless\GetDiscardPileTrait;
  use Effortless\BaseTableTrait;
  use Effortless\DatabaseTrait;
  use Effortless\DebugTrait;
  use Effortless\Setup;
  use Effortless\TurnOrderTrait;

  use Effortless\States\BotTurn;
  use Effortless\States\InitialSetup;
  use Effortless\States\Input;
  use Effortless\States\NextTurn;
  use Effortless\States\PlaceEffort;
  use Effortless\States\PostScoring;
  use Effortless\States\PreScoring;
  use Effortless\States\ResolveLocation;
  use Effortless\States\RoundUpkeep;
  use Effortless\States\TurnUpkeep;

  // public \Effortless\Utilities\DiceRoller $dice_roller;

  function __construct()
  {
    parent::__construct();
    self::initGameStateLabels([
      'bgaCurrentState' => BGA_GAMESTATE_CURRENT_STATE,

      'optionRuleset' => GAMEOPTION_RULESET,
      'optionAlteredRaceclass' => GAMEOPTION_ALTERED_RACECLASS,
      'optionHuntedThreats' => GAMEOPTION_HUNTED_THREATS,
    ]);
    // $this->dice_roller = new \Effortless\Utilities\DiceRoller();

    $this->mainDeck = new \WcLib\WcDeck(\Effortless\Models\Card::class, 'main');
    $this->locationDeck = new \WcLib\WcDeck(\Effortless\Models\Location::class, 'location');
    $this->settingDeck = new \WcLib\WcDeck(\Effortless\Models\Setting::class, 'setting');
    $this->valueStack = new \WcLib\ValueStack($this, GAMESTATE_JSON_RESOLVE_VALUE_STACK);

    $world = $this->world();
    $this->onEnteringState->addListener(function ($state_id) use ($world) {
      $this->paramInput_onEnteringState($world, $state_id);
    });
  }

  protected function getGameName()
  {
    return 'effortless';
  }

  // -----------
  // BGA framework entry points
  // -----------

  // Get all datas (complete reset request from client side)
  protected function getAllDatas()
  {
    $world = $this->world();

    // N.B.: Only immutable things should go here; anything mutable should go in `renderBoardState()` instead.
    return array_merge($this->renderBoardState(/*include_private=*/ false), [
      // // XXX: Do *we* have to send this, or is it already included?
      // 'players' => $this->renderForClient($world, Player::getAll($world)),
    ]);
  }

  function getGameProgression()
  {
    return 42;
  }

  // -----------
  // Misc
  // -----------

  // function varDumpToString($var)
  // {
  //   ob_start();
  //   var_dump($var);
  //   $result = ob_get_clean();
  //   return $result;
  // }

  function notifyDebug($scope, $msg): void
  {
    // // XXX: make this more configurable
    // if ($scope == "ResolveEffect") {
    //     return;
    // }

    if (php_sapi_name() == 'cli') {
      // XXX: This should be "if running under PHPUnit tests..."
      echo $scope . ': ' . $msg . "\n";
    }

    self::notifyAllPlayers('debugMessage', $scope . ': ' . $msg, []);
  }

  // -----------
  // Zombie players
  // -----------

  function zombieTurn($state, $active_player)
  {
    if ($state['name'] == 'playerTurn') {
      $this->gamestate->nextState('tZombiePass');
    } else {
      throw new BgaUserException('Zombie mode not supported at this game state: ' . $state['name']);
    }
  }

  function argGameEnd()
  {
    // // XXX: Return all scoring info so that the client can draw the end-game screen.

    // foreach (Seat::getAll($this->world()) as $seat) {
    //   $scoring_details = new PlayerScoringData();

    //   $items = [];

    //   foreach ($seat->cards($world) as $card) {
    //     if ($card instanceof AttributeCard) {
    //       // XXX:
    //     } elseif ($card instanceof ArmorCard) {
    //       // XXX:
    //     } elseif ($card instanceof ItemCard) {
    //       // XXX:
    //     } else {
    //       throw new \BgaVisibleSystemException('Unexpected card type during scoring.');
    //     }
    //   }
    // }

    // // Armor (1/4/8/13 per set)

    // // Items (points as printed on the item, if we have the attributes to utilize it)

    // // Attributes
    // //

    // // Settings (top to bottom)

    // // Tie breakers:
    // //
    // // - Highest single attribute score
    // // - Highest single scored item
    // // - Most complete armor sets

    // return array_merge($this->renderBoardState(), [
    //   'scoring' => \Effortless\calculateScores($this->world()),
    // ]);

    return array_merge(parent::argGameEnd(), [
      'scoringDetail' => \Effortless\calculateScores($this->world()),
    ]);
  }
}
