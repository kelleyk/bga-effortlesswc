<?php
/**
 * effortlesswc.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

$swdNamespaceAutoload = function ($class) {
  $classParts = explode('\\', $class);
  if (in_array($classParts[0], ['EffortlessWC', 'WcLib'])) {
    if ($classParts[0] == 'EffortlessWC') {
      array_shift($classParts);
    }
    $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
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

// // use EffortlessWC\Managers\Board;

// use EffortlessWC\Models\Player;

class Effortlesswc extends Table
{
  use EffortlessWC\Setup;
  use EffortlessWC\BaseTableTrait;

  use EffortlessWC\States\BotTurn;
  use EffortlessWC\States\InitialSetup;
  use EffortlessWC\States\Input;
  use EffortlessWC\States\NextTurn;
  use EffortlessWC\States\PlaceEffort;
  use EffortlessWC\States\PostScoring;
  use EffortlessWC\States\PreScoring;
  use EffortlessWC\States\ResolveLocation;
  use EffortlessWC\States\RoundUpkeep;
  use EffortlessWC\States\TurnUpkeep;

  // public \EffortlessWC\Utilities\DiceRoller $dice_roller;

  function __construct()
  {
    parent::__construct();
    self::initGameStateLabels([
      'optionRuleset' => GAMEOPTION_RULESET,
      'optionAlteredRaceclass' => GAMEOPTION_ALTERED_RACECLASS,
      'optionHuntedThreats' => GAMEOPTION_HUNTED_THREATS,
    ]);
    // $this->dice_roller = new \Effortlesswc\Utilities\DiceRoller();

    $this->mainDeck = new \WcLib\WcDeck(\EffortlessWC\Models\Card::class, 'main');
    $this->locationDeck = new \WcLib\WcDeck(\EffortlessWC\Models\Location::class, 'location');
    $this->settingDeck = new \WcLib\WcDeck(\EffortlessWC\Models\Setting::class, 'setting');
  }

  protected function getGameName()
  {
    return 'effortlesswc';
  }

  // -----------
  // BGA framework entry points
  // -----------

  // Get all datas (complete reset request from client side)
  protected function getAllDatas()
  {
    $world = $this->world();

    // N.B.: Only immutable things should go here; anything mutable should go in `renderBoardState()` instead.
    return array_merge($this->renderBoardState(), [
      // // XXX: Do *we* have to send this, or is it already included?
      // 'players' => $this->renderForClient($world, Player::getAll($world)),
    ]);
  }

  function getGameProgression()
  {
    return 42;
  }

  // -----------
  // Database interface
  // -----------

  function rawGetSeats()
  {
    return self::getCollectionFromDB('SELECT * FROM `seat` WHERE TRUE');
  }

  function rawGetPlayers()
  {
    return self::getCollectionFromDB('SELECT * FROM `player` WHERE TRUE');
  }

  function getEffortBySeat(int $location_index)
  {
    $rows = self::getCollectionFromDB('SELECT * FROM `effort` WHERE `location_index` = ' . $location_index);

    $effort = [];
    foreach ($rows as $row) {
      $effort[intval($row['seat_id'])] = intval($row['effort']);
    }
    return $effort;
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
}
