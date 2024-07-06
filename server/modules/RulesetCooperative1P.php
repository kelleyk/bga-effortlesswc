<?php declare(strict_types=1);

namespace EffortlessWC;

use EffortlessWC\Ruleset;

// "In Solo Cooperative Mode, there are a few changes to gameplay and scoring. Become Famous to win!
//
// Before the game, set aside 2 sets of Effort and assign one of the 2 dice to each set of Effort. Also decide which
// one will be first in turn order for the remainder of the game.
//
// These will represent AI oponents. Their cards will affect Attribute scoring and their Effort will effect Most and
// Least scoring at Locations. Each of them will end the game with a separate hand of 20 cards.
//
// After each of your turns, before rolling a die for the Threats, roll both dice. Place an effort from each AI player
// at the location rolled by their corresponding die and add 1 card from the location to each of their hands. If a
// location has more than one card, choose which card they take.
//
// AI players DO NOT activate locations, fight Threats, or score Greatness.
//
// During scoring, use the AI players’ individual hands to score their Attributes against your own. Then, use their
// Effort at Locations when scoring for Most and Least Effort."
class RulesetCooperative1P extends Ruleset
{
  // onSetup
// - add two bot seats
// onBotTurn
// - the bot turn happens the solo player turn and *before* the threat phase!
// - they randomly choose a location, but they only place an effort
//   - the rest of the turn (activate location, fight threats, score greatness) does not happen
//   - player input might be necessary if there are multiple cards at the location
// scoring
// - (as above)
}
