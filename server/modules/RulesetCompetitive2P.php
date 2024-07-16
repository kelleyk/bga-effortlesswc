<?php declare(strict_types=1);

namespace EffortlessWC;

use EffortlessWC\Ruleset;

function array_rand_value($array)
{
  $key = array_rand($array);
  return $array[$key];
}

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
  // scoring
  // - the bot player's effort piles count for scoring, but the bot itself does not score

  public function onBotTurn(World $world)
  {
    // Randomly choose a location.
    $location = array_rand_value($world->locations());

    // Move one effort from the active seat's reserve to their effort-pile at that location.
    $world->moveEffort(
      $world->activeSeat()->reserveEffort($world),
      $location->effortPileForSeat($world, $world->activeSeat())
    );

    // Discard all cards; they will be replaced from the deck during ST_TURN_UPKEEP.
    foreach ($location->cards($world) as $card) {
      $world->discardCard($card);
    }

    $world
      ->table()
      ->notifyAllPlayers(
        'XXX_message',
        'Bot turn (2P competitive): random location visited; location ability not activated; cards there discarded and replaced. Location=${location}',
        [
          'location' => $location->renderForNotif($world),
        ]
      );
  }
}
