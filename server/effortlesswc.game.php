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

// require_once 'modules/php/Models/Chip.php';
// require_once 'modules/php/Models/Destination.php';
// require_once 'modules/php/Models/Entity.php';
// require_once 'modules/php/Models/EventCard.php';
// require_once 'modules/php/Models/Finale.php';
// require_once 'modules/php/Models/MiscEntity.php';
// require_once 'modules/php/Models/Npc.php';
// require_once 'modules/php/Models/PlayerCharacter.php';
// require_once 'modules/php/Models/Position.php';
// require_once 'modules/php/Models/Tile.php';
// require_once 'modules/php/Models/Token.php';
// require_once 'modules/php/Models/Wall.php';

// // use EffortlessWC\Managers\Board;

// use EffortlessWC\Models\Chip;
// use EffortlessWC\Models\Entity;
// use EffortlessWC\Models\Position;
// use EffortlessWC\Models\Tile;

class Effortlesswc extends Table
{
  // use EffortlessWC\Bouncer;
  // use EffortlessWC\ClientRender;
  // use EffortlessWC\DataLayer;
  // use EffortlessWC\GameEffects;
  // use EffortlessWC\GameEffectUtils;
  // use EffortlessWC\GameFlow;
  // use EffortlessWC\GameOptions;
  use EffortlessWC\Setup;
  // use EffortlessWC\TurnOrder;
  // use EffortlessWC\WorldImpl;
  // use EffortlessWC\Logging;

  // use EffortlessWC\Models\EntityManager;
  // use EffortlessWC\Models\FinaleManager;
  // use EffortlessWC\Models\TileManager;
  // use EffortlessWC\Models\WallManager;

  // use EffortlessWC\Utilities\GameState;

  use EffortlessWC\States\InitialSetup;
  use EffortlessWC\States\NextTurn;
  use EffortlessWC\States\PlaceEffort;

  // public \EffortlessWC\Utilities\DiceRoller $dice_roller;

  public \WcLib\WcDeck $mainDeck;
  public \WcLib\WcDeck $locationDeck;
  public \WcLib\WcDeck $settingDeck;

  function __construct()
  {
    parent::__construct();
    self::initGameStateLabels([
      'optionRuleset' => GAMEOPTION_RULESET,
      'optionAlteredRaceclass' => GAMEOPTION_ALTERED_RACECLASS,
      'optionHuntedThreats' => GAMEOPTION_HUNTED_THREATS,
    ]);
    // $this->dice_roller = new \Effortlesswc\Utilities\DiceRoller();

    $this->mainDeck = new \WcLib\WcDeck($this, 'main');
    $this->locationDeck = new \WcLib\WcDeck($this, 'location');
    $this->settingDeck = new \WcLib\WcDeck($this, 'setting');
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
    // // XXX: things to add ...
    // // - character hands
    // // - character statuses
    // // - finale, if visible, and finale active/inactive state
    // // - discard piles & number of cards deck (plus number of
    // //   cards moved for patrol decks?)
    // // - patrol route(s)
    // return [
    //   'gamemap' => self::renderGameMapForClient(self::rawGetTiles(), self::getWalls()),
    //   'characters' => self::renderPlayerCharactersForClient(self::getPlayerCharacters()),
    //   'entities' => self::renderEntitiesForClient(self::rawGetEntities()),
    //   'decks' => self::getAndRenderAllDecksForClient(),
    //   'gameFlowSettings' => self::getGameFlowSettingsForClient(),
    //   'finale' => self::renderFinaleForClient(),
    //   // N.B.: This includes the patrol-path data for bouncer
    //   // NPCs.
    //   'npcs' => self::renderNpcsForClient(self::rawGetNpcs()),
    //   'tableStatuses' => self::renderStatusesForClient(self::getGameStateJson(GAMESTATE_JSON_TABLE_STATUSES)),
    // ];

    return [];
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
}
