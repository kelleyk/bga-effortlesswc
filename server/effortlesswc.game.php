<?php
/**
  * expressionswc.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  */

$swdNamespaceAutoload = function ($class) {
  $classParts = explode('\\', $class);
  if ($classParts[0] == 'Expressionswc') {
    array_shift($classParts);
    $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
      var_dump('Cannot find file: ' . $file);
    }
  }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

// require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

// require_once 'modules/php/card_data.inc.php';
// require_once 'modules/php/constants.inc.php';

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

// // use ExpressionsWC\Managers\Board;

// use ExpressionsWC\Models\Chip;
// use ExpressionsWC\Models\Entity;
// use ExpressionsWC\Models\Position;
// use ExpressionsWC\Models\Tile;

class Expressionswc extends Table
{
  // use ExpressionsWC\Bouncer;
  // use ExpressionsWC\ClientRender;
  // use ExpressionsWC\DataLayer;
  // use ExpressionsWC\GameEffects;
  // use ExpressionsWC\GameEffectUtils;
  // use ExpressionsWC\GameFlow;
  // use ExpressionsWC\GameOptions;
  use ExpressionsWC\Setup;
  // use ExpressionsWC\TurnOrder;
  // use ExpressionsWC\WorldImpl;
  // use ExpressionsWC\Logging;

  // use ExpressionsWC\Models\EntityManager;
  // use ExpressionsWC\Models\FinaleManager;
  // use ExpressionsWC\Models\TileManager;
  // use ExpressionsWC\Models\WallManager;

  // use ExpressionsWC\Utilities\GameState;

  // use ExpressionsWC\States\ActionWindow;
  // use ExpressionsWC\States\CharacterSelection;
  // use ExpressionsWC\States\CharacterSelectionRoundEnd;
  // use ExpressionsWC\States\FinishSetup;
  // use ExpressionsWC\States\NextCharacter;
  // use ExpressionsWC\States\NpcTurn;
  // use ExpressionsWC\States\PlaceEntranceTokens;
  // use ExpressionsWC\States\PlayerTurn;
  // use ExpressionsWC\States\PlayerTurnEnds;
  // use ExpressionsWC\States\PlayerTurnEnterMap;
  // use ExpressionsWC\States\ResolveEffect;
  // use ExpressionsWC\States\TargetSelection;

  // public \ExpressionsWC\Utilities\DiceRoller $dice_roller;

  function __construct()
  {
    parent::__construct();
    self::initGameStateLabels([
      // 'optionCharacterSelection' => OPTION_CHARACTER_SELECTION,
      // // "optionCharacterVariants" => OPTION_CHARACTER_VARIANTS,
      // 'optionSuspicion' => OPTION_SUSPICION,
      // 'optionFinale' => OPTION_FINALE,
      // 'optionMultihanded' => OPTION_MULTIHANDED,
      // 'optionWallPlacement' => OPTION_WALL_PLACEMENT,
      // 'optionWallRerolling' => OPTION_WALL_REROLLING,
      // 'optionVariantDeadDrops' => OPTION_VARIANT_DEAD_DROPS,
      // 'optionVariantCasingTheJoint' => OPTION_VARIANT_CASING_THE_JOINT,
    ]);
    // $this->dice_roller = new \Expressionswc\Utilities\DiceRoller();
  }

  protected function getGameName()
  {
    return 'expressionswc';
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
