<?php
declare(strict_types=1);
/*
 * THIS FILE HAS BEEN AUTOMATICALLY GENERATED. ANY CHANGES MADE DIRECTLY MAY BE OVERWRITTEN.
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * EffortlessWC implementation : © Kevin Kelley <kelleyk@kelleyk.net>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

/**
 * TYPE CHECKING ONLY, this function is never called.
 * If there are any undefined function errors here, you MUST rename the action within the game states file, or create the function in the game class.
 * If the function does not match the parameters correctly, you are either calling an invalid function, or you have incorrectly added parameters to a state function.
 */
if (false) {
  /** @var effortlesswc $game */
}

$machinestates = [
  ST_BGA_GAME_SETUP => [
    'name' => 'gameSetup',
    'description' => '',
    'type' => 'manager',
    'action' => 'stGameSetup',
    'transitions' => [
      '' => ST_INITIAL_SETUP,
    ],
  ],

  ST_BGA_GAME_END => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd',
  ],

  ST_INITIAL_SETUP => [
    'name' => 'stInitialSetup',
    'action' => 'stInitialSetup',
    'description' => clienttranslate('...'),
    'type' => 'game',
    'transitions' => [
      // TODO: When we implement Altered's races and classes, this will go to ST_ALTERED_INPUT instead.
      T_DONE => ST_NEXT_TURN,
    ],
  ],

  // TODO: ST_ALTERED_INPUT and ST_PREGAME -- we'll need to add those states when we start implementing expansions.

  ST_NEXT_TURN => [
    'name' => 'stNextTurn',
    'action' => 'stNextTurn',
    'description' => clienttranslate('...'),
    'type' => 'game',
    'transitions' => [
      T_BEGIN_HUMAN_TURN => ST_PLACE_EFFORT,
      T_BEGIN_BOT_TURN => ST_BOT_TURN,
      T_END_GAME => ST_TRIGGER_END_GAME,
    ],
  ],

  ST_PLACE_EFFORT => [
    'name' => 'stPlaceEffort',
    'action' => 'stPlaceEffort',
    'args' => 'argPlaceEffort',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_GET_INPUT => ST_INPUT,
      T_DONE => ST_RESOLVE_LOCATION,
    ],
  ],

  ST_RESOLVE_LOCATION => [
    'name' => 'stResolveLocation',
    'action' => 'stResolveLocation',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_GET_INPUT => ST_INPUT,
      T_DONE => ST_TURN_UPKEEP,
    ],
  ],

  ST_TURN_UPKEEP => [
    'name' => 'stTurnUpkeep',
    'action' => 'stTurnUpkeep',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_ROUND_UPKEEP => ST_ROUND_UPKEEP,
      T_DONE => ST_NEXT_TURN,
    ],
  ],

  ST_ROUND_UPKEEP => [
    'name' => 'stRoundUpkeep',
    'action' => 'stRoundUpkeep',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_DONE => ST_TURN_UPKEEP,
      // T_FIGHT_THREAT => ST_DRAW_THREAT,  // TODO: when we implement Hunted's threats
    ],
  ],

  // TODO: ST_DRAW_THREAT, ST_FIGHT_THREAT when we implement Hunted's threats

  ST_BOT_TURN => [
    'name' => 'stBotTurn',
    'action' => 'stBotTurn',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_GET_INPUT => ST_INPUT,
      T_DONE => ST_TURN_UPKEEP,
    ],
  ],

  // The player needs to select one or more objects on the field.
  ST_INPUT => [
    'name' => 'stInput',
    'action' => 'stInput',
    'args' => 'argInput',
    'type' => 'multipleactiveplayer',
    'description' => '${input.description}',
    'descriptionmyturn' => '${input.descriptionmyturn}',
    'possibleactions' => [
      'actSelectInput',
      // 'actCancel',  // XXX: Can any actions in Effortless be canceled?
    ],
    'transitions' => [
      T_RET_PLACE_EFFORT => ST_PLACE_EFFORT,
      T_RET_RESOLVE_LOCATION => ST_RESOLVE_LOCATION,
      T_RET_BOT_TURN => ST_BOT_TURN,
      T_RET_PRE_SCORING => ST_PRE_SCORING,
      // TODO: Need more `T_RET_*` things
    ],
  ],

  ST_PRE_SCORING => [
    'name' => 'stPreScoring',
    'action' => 'stPreScoring',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_GET_INPUT => ST_INPUT,
      T_DONE => ST_POST_SCORING,
    ],
  ],

  ST_POST_SCORING => [
    'name' => 'stPostScoring',
    'action' => 'stPostScoring',
    'args' => 'argPostScoring',
    'type' => 'game',
    'description' => clienttranslate('...'),
    'transitions' => [
      T_DONE => ST_BGA_GAME_END,
    ],
  ],
];
