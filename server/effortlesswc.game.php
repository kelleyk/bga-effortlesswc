<?php
/**
 * effortlesswc.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

$swdNamespaceAutoload = function ($class) {
  // echo '*** # registered autoloaders = ' . count(spl_autoload_functions()) . "\n";
  // foreach (spl_autoload_functions() as $loader) {
  //   echo '  - ' . print_r($loader,true) . "\n";
  // }
  // echo '*** effortlesswc autoloader got called with $class=' . $class . "\n";

  $classParts = explode('\\', $class);
  if (in_array($classParts[0], ['EffortlessWC', 'WcLib'])) {
    if ($classParts[0] == 'EffortlessWC') {
      array_shift($classParts);
    }
    $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
      // var_dump('Cannot find file: ' . $file);
      echo '*** effortlesswc autoloader cannot find import for $class=' . $class . "\n";
    }
  }
  // else {
  //   echo '  *** effortlesswc autoloader ignoring; not in matching namespace' . "\n";
  // }
};
spl_autoload_register($swdNamespaceAutoload, true, false);

// @phan-suppress-next-line PhanUndeclaredConstant
require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

// require_once 'modules/php/card_data.inc.php';
require_once 'modules/php/constants.inc.php';

// require_once 'modules/php/Models/Card.php';
// require_once 'modules/php/Models/Location.php';
// require_once 'modules/php/Models/Setting.php';

// // use EffortlessWC\Managers\Board;

use EffortlessWC\Models\Player;

// use EffortlessWC\Models\Entity;
// use EffortlessWC\Models\Position;
// use EffortlessWC\Models\Tile;

class Effortlesswc extends Table
{
  use EffortlessWC\Setup;
  use EffortlessWC\BaseTableTrait;

  use EffortlessWC\States\InitialSetup;
  use EffortlessWC\States\NextTurn;
  use EffortlessWC\States\PlaceEffort;

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
      // XXX: Do *we* have to send this, or is it already included?
      'players' => $this->renderForClient($world, Player::getAll($world)),
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
