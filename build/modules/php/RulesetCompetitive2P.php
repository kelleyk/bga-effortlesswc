<?php declare(strict_types=1);

namespace EffortlessWC;

use EffortlessWC\Ruleset;

// "Setup stays mostly the same for a 2 player game with the exception of adding a die and an additional set of 20
// Effort.
//
// During a 2 player game, players will add in an automated player that will place effort and discard cards.
//
// Each round, after both players have taken a turn, one player rolls one of the six sided dice. The automated player
// now places an effort at the location relative to the rolled number (from top to bottom). Then, players discard ALL
// cards at that location and replace them from the top of the deck.
//
// The automated player DOES NOT score greatness, make decisions, or perform any location abilities. However, the
// automated player’s Effort DOES affect Setting scoring."
//
class RulesetCompetitive2P extends Ruleset
{
  // onSetup
// - add one bot seat
// onBotTurn
// - randomly choose a location
// - place an effort there
// - discard all cards and replace them from the deck
// scoring
// - the bot player's effort piles count for scoring, but the bot itself does not score
}
