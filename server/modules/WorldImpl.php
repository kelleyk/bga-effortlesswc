<?php declare(strict_types=1);

namespace EffortlessWC;

require_once 'WcLib/WcDeck.php';

use EffortlessWC\Models\Card;
use EffortlessWC\Models\EffortPile;
use EffortlessWC\Models\Location;
use EffortlessWC\Models\Seat;
use EffortlessWC\Models\Setting;
use EffortlessWC\Ruleset;

class WorldImpl implements World
{
  private $table_;

  function __construct($table)
  {
    $this->table_ = $table;
  }

  public function table()
  {
    return $this->table_;
  }

  public function nextState(string $transition): void
  {
    $this->table()->gamestate->nextState($transition);
  }

  public function fillCards(Location $loc): void
  {
    $cards_face_up_qty = 0;
    $cards_face_down_qty = 0;
    foreach ($loc->cards($this) as $card) {
      if ($card->isFaceDown()) {
        ++$cards_face_down_qty;
      } else {
        ++$cards_face_up_qty;
      }
    }

    // echo '*** fillCards() for loc id=' .
    //   $loc->id() .
    //   ' type=' .
    //   get_class($loc) .
    //   ": {$cards_face_up_qty} {$cards_face_down_qty} " .
    //   $loc->cardsFaceUp() .
    //   ' ' .
    //   $loc->cardsFaceDown() .
    //   "\n";

    if ($cards_face_up_qty > $loc->cardsFaceUp()) {
      throw new \BgaVisibleSystemException('Too many face-up cards in location.');
    }
    if ($cards_face_down_qty > $loc->cardsFaceDown()) {
      throw new \BgaVisibleSystemException('Too many face-down cards in location.');
    }

    for ($i = $cards_face_up_qty; $i < $loc->cardsFaceUp(); ++$i) {
      $card = $this->table()->mainDeck->drawTo('SETLOC', $loc->locationArg());
      $card->setFaceDown(false);
      // echo '*** drawing card id=' . $card->id() . ' to loc id=' . $loc->id() . ' face-down...' . "\n";
    }
    for ($i = $cards_face_down_qty; $i < $loc->cardsFaceDown(); ++$i) {
      $card = $this->table()->mainDeck->drawTo('SETLOC', $loc->locationArg());
      $card->setFaceDown(true);
    }
  }

  // Map from seat ID to effort count.
  public function effortBySeat(Setting $setting)
  {
    throw new \feException('no impl: effortbyseat');
  }

  // Returns an array whose keys are the same as $effort_by_seat.  Values are in the range [1, 5], where key(s) with the
  // value 1 have, or are tied for, the largest values in $effort_by_seat, and so on.  If $invert == false, rank 1 will
  // be given to the lowest values instead.
  //
  // $outcome_good should be true iff the ranking is for something that players *want* (e.g. postive points), and false
  // iff it is for something that players do not want (e.g. negative points).  This is important when tie-breaking.
  //
  // TODO: This function will account for things like the Fighter's tie-breaking ability.
  public function rankByEffort($effort_by_seat, bool $outcome_good, bool $invert = false)
  {
    throw new \feException('no impl: rankbyeffort');
  }

  // Like `rankByEffort()` but returns a list of the seat IDs at rank 1.
  public function topByEffort($effort_by_seat, bool $outcome_good, bool $invert = false)
  {
    throw new \feException('no impl: topbyeffort');
  }

  public function allEffortPiles()
  {
    throw new \feException('no impl: alleffortpiles');
  }

  public function locations()
  {
    // XXX: We need this to return only those in play.
    return Location::getAll($this);
  }

  public function activeSeat(): Seat
  {
    return Seat::mustGetById($this, $this->table()->getGameStateInt(GAMESTATE_INT_ACTIVE_SEAT));
  }

  public function ruleset(): Ruleset
  {
    $option_ruleset = $this->table()->getGameStateValue('optionRuleset');
    switch ($option_ruleset) {
      case GAMEOPTION_RULESET_COMPETITIVE:
        switch ($this->table()->getPlayersNumber()) {
          case 1:
            return new \EffortlessWC\RulesetCompetitive1P();
          case 2:
            return new \EffortlessWC\RulesetCompetitive2P();
          default:
            return new \EffortlessWC\RulesetCompetitive();
        }
      case GAMEOPTION_RULESET_COOPERATIVE:
        switch ($this->table()->getPlayersNumber()) {
          case 1:
            return new \EffortlessWC\RulesetCooperative1P();
          default:
            return new \EffortlessWC\RulesetCooperative();
        }
    }
    throw new \BgaVisibleSystemException('Unexpected value for GAMEOPTION_RULESET: ' . $option_ruleset);
  }

  public function visitedLocation(): Location
  {
    return Location::mustGetById($this, $this->table()->getGameStateInt(GAMESTATE_INT_VISITED_LOCATION));
  }

  public function moveCardToLocation(Card $card, Location $loc)
  {
    throw new \feException('no impl: movecardtolocation');
  }

  public function moveCardToHand(Card $card, Seat $seat)
  {
    throw new \feException('no impl: movecardtohand');
  }

  // This is roughly `moveCardToHand()` from the deck.
  public function drawCardToHand(Seat $seat)
  {
    throw new \feException('no impl: drawcardtohand');
  }

  public function discardCard(Card $card)
  {
    throw new \feException('no impl: discardcard');
  }

  // Moves one effort from $src to $dst.  The piles must have the same `seat()` and different `location()`.
  public function moveEffort(EffortPile $src, EffortPile $dst)
  {
    // XXX: Send notif so that client can animate.

    $src->addEffort($this, -1);
    $dst->addEffort($this, 1);
  }
}
