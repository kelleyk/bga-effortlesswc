<?php declare(strict_types=1);

namespace Effortless;

use Effortless\Ruleset;

// "In Solo mode, you will be facing off against 2 other adventures destined to best you.
//
// To start, setup for a 3 player game, selecting 2 other sets of Effort to play against. Then, assign one of the die
// to each set of Effort and determine turn order starting with yourself.
//
// You take turns as normal, however: after each of your turns roll both of the dice and take turns for the other
// adventurers based on the numbers rolled. Place one Effort on the rolled Location and add a card to their
// hand. Anytime that the other adventurers would need to make a decision, whether it is moving Effort or choosing a
// card, you get to make that decision for them.
//
// At the end of the game, score all players as normal. Then, add the other adventurers’ scores together. If your
// score is higher than their combined score, you win!
//
// For an added challenge, instead of scoring the other adventurers separately and adding them together, score them as
// one player, combining their cards and Effort."
//
class RulesetCompetitive1P extends Ruleset
{
  // onSetup
// - add two bot seats
// onBotTurn
// - randomly choose a location
// - turn proceeds normally, but with deciding player (the solo player) making decisions
// scoring
// - optional: the "added challenge" scoring mode
}
