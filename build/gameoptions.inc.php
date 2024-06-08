<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * theshipwreckarcana implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * theshipwreckarcana game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in theshipwreckarcana.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

require_once 'modules/php/constants.inc.php';

$game_options = [
  GAMEOPTION_RULESET => [
    'name' => totranslate('Ruleset'),
    'values' => [
      GAMEOPTION_RULESET_COMPETITIVE => [
        'name' => totranslate('Competitive'),
        'tmdisplay' => totranslate('Competitive'),
        'description' => totranslate('The players will compete with each other.  These are the normal rules.'),
      ],
      GAMEOPTION_RULESET_COOPERATIVE => [
        'name' => totranslate('Cooperative'),
        'tmdisplay' => totranslate('Cooperative'),
        // XXX:
        'description' => totranslate(
          'The players will work together.  These rules are from the Altered expansion.  Threats are always used when playing cooperatively, which makes the game a little bit more complex.'
        ),
      ],
    ],
    'default' => GAMEOPTION_RULESET_COMPETITIVE,
  ],

  // GAMEOPTION_SETLOC_SETS => [
  //   // XXX: - Base-game only
  //   // XXX: - All (* but not threat mechanic stuff if threats are not in use)
  //   // XXX: - Maybe a "remove all negative-scoring settings" option?
  // ],

  // XXX: Remove "human" as race option -- it's mostly for kids or whatever.
  //
  // XXX: Possible future feature -- "beginner selection" for races/classes.
  GAMEOPTION_ALTERED_RACECLASS => [
    'name' => totranslate('Play with race and class module'),
    'values' => [
      GAMEOPTION_DISABLED => [
        'name' => totranslate('Disabled'),
        'description' => totranslate('The races and classes from the Altered expansion will not be used.'),
      ],
      GAMEOPTION_ENABLED => [
        'name' => totranslate('Enabled'),
        'tmdisplay' => totranslate('With races and classes'),
        'description' => totranslate('The races and classes from the Altered expansion will be used.'),
      ],
    ],
    'default' => GAMEOPTION_DISABLED,
  ],
  # N.B.: This is forced on in Cooperative mode.
  GAMEOPTION_HUNTED_THREATS => [
    'name' => totranslate('Play with Threat module'),
    'values' => [
      GAMEOPTION_DISABLED => [
        'name' => totranslate('Disabled'),
        'description' => totranslate('The Threats from the Hunted expansion will not be used.'),
      ],
      GAMEOPTION_ENABLED => [
        'name' => totranslate('Enabled'),
        'tmdisplay' => totranslate('With Threats'),
        'description' => totranslate('The Threats from the Hunted expansion will be used.'),
      ],
    ],
    'default' => GAMEOPTION_DISABLED,
    'displaycondition' => [
      [
        'type' => 'otheroption',
        'id' => GAMEOPTION_RULESET,
        'value' => [GAMEOPTION_RULESET_COMPETITIVE],
      ],
    ],
  ],

  // Add Experience cards to deck at setup?
  // XXX: 6 cards
];
