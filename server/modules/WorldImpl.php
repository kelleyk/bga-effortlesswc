<?php declare(strict_types=1);

namespace Effortless;

require_once 'WcLib/WcDeck.php';

use Effortless\Models\Card;
use Effortless\Models\EffortPile;
use Effortless\Models\Location;
use Effortless\Models\Seat;
use Effortless\Models\Setting;
use Effortless\Ruleset;

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
  //
  // XXX: Decide where this logic should live (on `Setting` or on `World`) and remove the other.
  public function effortBySeat(Setting $setting)
  {
    return $setting->effortBySeat($this);
  }

  // Returns an array whose keys are the same as $effort_by_seat.  Values are in the range [1, 5], where key(s) with the
  // value 1 have, or are tied for, the largest values in $effort_by_seat, and so on.  If $invert == false, rank 1 will
  // be given to the lowest values instead.
  //
  // $outcome_good should be true iff the ranking is for something that players *want* (e.g. postive points), and false
  // iff it is for something that players do not want (e.g. negative points).  This is important when tie-breaking.
  //
  // TODO: This function will account for things like the Fighter's tie-breaking ability; when we implement that, we'll
  // use $outcome_good to decide how ties are broken.
  //
  // XXX: Some version of this could almost certainly be cleaned up and moved to WcLib for reuse!
  //
  // XXX: This definitely needs some unit tests!
  public function rankByEffort($effort_by_seat, bool $outcome_good, bool $invert = false)
  {
    // Sort highest-first (if !$invert) or lowest-first (if $invert).
    uasort($effort_by_seat, function ($a, $b) use ($invert) {
      if ($invert) {
        return $a - $b;
      }
      return $b - $a;
    });

    $rank_by_seat = [];

    $last_rank = 0;
    $last_rank_value = -1;
    $i = 0;
    foreach ($effort_by_seat as $seat_id => $qty) {
      ++$i;

      if ($qty != $last_rank_value) {
        $last_rank = $i;
        $last_rank_value = $qty;
      }

      $rank_by_seat[$seat_id] = $last_rank;
    }

    return $rank_by_seat;
  }

  // Like `rankByEffort()` but returns a list of the seat IDs at rank 1.
  /** @return int[] */
  public function topByEffort($effort_by_seat, bool $outcome_good, bool $invert = false)
  {
    $rank_by_seat = $this->rankByEffort($effort_by_seat, $outcome_good, $invert);

    return array_keys(
      array_filter($rank_by_seat, function ($rank) {
        return $rank === 1;
      })
    );
  }

  public function allEffortPiles(bool $include_reserve_piles = false)
  {
    $piles = EffortPile::getAll($this);
    if (!$include_reserve_piles) {
      $piles = array_values(
        array_filter($piles, function ($pile) {
          return $pile->hasLocation();
        })
      );
    }
    return $piles;
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
      // XXX: The "case 0" is a temporary hack to unstick tables created without these gameoptions enabled.
      case 0:
      case GAMEOPTION_RULESET_COMPETITIVE:
        switch ($this->table()->getPlayersNumber()) {
          case 1:
            return new \Effortless\RulesetCompetitive1P();
          case 2:
            return new \Effortless\RulesetCompetitive2P();
          default:
            return new \Effortless\RulesetCompetitive();
        }
      case GAMEOPTION_RULESET_COOPERATIVE:
        switch ($this->table()->getPlayersNumber()) {
          case 1:
            return new \Effortless\RulesetCooperative1P();
          default:
            return new \Effortless\RulesetCooperative();
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
    $this->table()->mainDeck->placeOnTop($card, 'SETLOC', $loc->locationArg());

    // XXX: Send more appropriate notifs.
    $this->table()->notifyAllPlayers('debug', 'moveCardToLocation(): card=${card} loc=${loc}', [
      'card' => $card->renderForNotif($this),
      'loc' => $loc->renderForNotif($this),
    ]);
  }

  public function moveCardToHand(Card $card, Seat $seat)
  {
    // TODO: XXX: If we take a face-down card, I imagine that it remains secret (?).

    $face_down = $card->isFaceDown();
    $card->setFaceDown(false);

    // XXX: This will send multiple notifs when the player draws multiple cards.
    switch ($card->sublocation()) {
      case 'SETLOC':
        if ($face_down) {
          // XXX: Send more appropriate notifs.
          $this->table()->notifyAllPlayers('debug', '${seat} takes a card from ${location}.', [
            'seat' => $seat->renderForNotif($this),
            'location' => $card->gameLocation($this)->renderForNotif($this),
          ]);
        } else {
          // XXX: Send more appropriate notifs.
          $this->table()->notifyAllPlayers('debug', '${seat} takes ${card} from ${location}.', [
            'card' => $card->renderForNotif($this),
            'seat' => $seat->renderForNotif($this),
            'location' => $card->gameLocation($this)->renderForNotif($this),
          ]);
        }
        break;
      case 'DISCARD':
        if ($face_down) {
          throw new \BgaVisibleSystemException('Cards in the discard pile should never be face-down.');
        }
        $this->table()->notifyAllPlayers('debug', '${seat} takes ${card} from the discard pile.', [
          'card' => $card->renderForNotif($this),
          'seat' => $seat->renderForNotif($this),
        ]);
        break;
      case 'DECK':
        if (!$face_down) {
          throw new \BgaVisibleSystemException('Cards in the deck should always be face-down.');
        }
        $this->table()->notifyAllPlayers('debug', '${seat} draws a card from the deck.', [
          'seat' => $seat->renderForNotif($this),
        ]);
        break;
    }

    $this->table()->mainDeck->placeOnTop($card, 'HAND', $seat->id());
  }

  // This is roughly `moveCardToHand()` from the deck.
  public function drawCardToHand(Seat $seat)
  {
    $card = $this->table()->mainDeck->peekTop();
    if ($card === null) {
      // XXX: look up appropriate behavior in rules.  autoshuffle?
      throw new \BgaVisibleSystemException('XXX: deck is empty');
    }

    $card->setFaceDown(false);

    $this->table()->mainDeck->placeOnTop($card, 'HAND', $seat->id());

    // XXX: Send more appropriate notifs.
    $this->table()->notifyAllPlayers('debug', 'drawCardToHand(): card=${card} seat=${seat}', [
      'card' => $card->renderForNotif($this),
      'seat' => $seat->renderForNotif($this),
    ]);
  }

  public function discardCard(Card $card)
  {
    $face_down = $card->isFaceDown();
    $card->setFaceDown(false);

    switch ($card->sublocation()) {
      case 'SETLOC':
        if ($face_down) {
          // XXX: Send more appropriate notifs.
          $this->table()->notifyAllPlayers('debug', 'A card is discarded from ${location}.', [
            'location' => $card->gameLocation($this)->renderForNotif($this),
          ]);
        } else {
          // XXX: Send more appropriate notifs.
          $this->table()->notifyAllPlayers('debug', '${card} is discarded from ${location}.', [
            'card' => $card->renderForNotif($this),
            'location' => $card->gameLocation($this)->renderForNotif($this),
          ]);
        }
        break;
      case 'HAND':
        $seat = Seat::mustGetById($this, $card->sublocationIndex());
        if ($face_down) {
          // XXX: Send more appropriate notifs.
          $this->table()->notifyAllPlayers('debug', '${seat} discards a card from their hand.', [
            'seat' => $seat->renderForNotif($this),
          ]);
        } else {
          // XXX: Send more appropriate notifs.
          $this->table()->notifyAllPlayers('debug', '${seat} discards ${card} from their hand.', [
            'card' => $card->renderForNotif($this),
            'seat' => $seat->renderForNotif($this),
          ]);
        }
        break;
      case 'DECK':
        // No notif for this.
        break;
    }

    $this->table()->mainDeck->placeOnTop($card, 'DISCARD');
  }

  // Moves one effort from $src to $dst.  The piles must have the same `seat()` and different `location()`.
  public function moveEffort(EffortPile $src, EffortPile $dst)
  {
    // XXX: Send notif so that client can animate.

    $src->addEffort($this, -1);
    $dst->addEffort($this, 1);
  }
}
