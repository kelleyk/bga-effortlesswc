<?php declare(strict_types=1);

namespace EffortlessWC;

require_once 'modules/Models/Seat.php';

abstract class Location
{
  // XXX: The `getParameter()` stuff should be moved to their own trait; they throw a special exception if we need to
  // ask for user input still.
  public function getParameterEffortPile(World $world, int $param_index, $valid_targets)
  {
    throw new \feException('XXX: foo');
  }

  public function getParameterLocation(World $world, int $param_index, $valid_targets)
  {
    throw new \feException('XXX: foo');
  }

  public function getParameterCardInHand(World $world, int $param_index, Seat $seat)
  {
    // XXX: This should be a thin layer over `getParameterCard()`.
    throw new \feException('XXX: foo');
  }

  // XXX: This needs to show the player making the decision any face-down cards when they make their decision.
  public function getParameterCardAtLocation(World $world, int $param_index, Location $loc)
  {
    // XXX: This should be a thin layer over `getParameterCard()`.
    throw new \feException('XXX: foo');
  }

  // Each element of $valid_targets is a `Card`.
  public function getParameterCard(World $world, int $param_index, $valid_targets)
  {
    throw new \feException('XXX: foo');
  }

  public function cards(World $world)
  {
    throw new \feException('XXX: foo');
  }

  public function effortPileForSeat(World $world, Seat $seat): EffortPile
  {
    throw new \feException('XXX: foo');
  }

  // XXX: returns {seatId: EffortPile}  ... or should it return SeatPile[]?
  public function effortPiles(World $world)
  {
    throw new \feException('XXX: foo');
  }

  // Returns `Location[]`.
  public function adjacentLocations(World $world)
  {
    throw new \feException('XXX: foo');
  }

  public function isAdjacentTo(World $world, Location $other)
  {
    throw new \feException('XXX: foo');
  }

  // XXX: Should we remove $seat and just use $this->activeSeat() instead?
  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: foo');
  }

  public function cardsFaceUp(): int
  {
    $rc = new \ReflectionClass(self::class);
    return $rc->hasConstant('CARDS_FACE_UP') ? self::CARDS_FACE_UP : 0;
  }

  public function cardsFaceDown(): int
  {
    $rc = new \ReflectionClass(self::class);
    return $rc->hasConstant('CARDS_FACE_DOWN') ? self::CARDS_FACE_DOWN : 0;
  }
}

// XXX: An "effort pile" is a (setloc, seat) pair.

class CaveLocation extends Location
{
  const LOCATION_ID = 'location:cave';
  const SET_ID = SET_BASE;

  const CARDS_FACE_DOWN = 1;

  public function onVisited(World $world, Seat $seat)
  {
    // No effects.
  }
}

// "Move one of your effort from any other location to here."
class CityLocation extends Location
{
  const LOCATION_ID = 'location:city';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToLocation($this->getParameterCardInHand($world, 0), $this);

    foreach ($this->cards($world) as $card) {
      $world->moveCardToHand($card, $world->activeSeat());
    }
  }
}

// "Take 1 card from here and discard the other."
class ColiseumLocation extends Location
{
  const LOCATION_ID = 'location:coliseum';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $selected_card = $this->getParameterCardAtLocation($world, 0);

    $other_cards = array_values(
      array_filter(function ($card) use ($this, $world) {
        return $card->id() != $selected_card->id();
      }),
      $this->cards($world)
    );
    if (count($other_cards) != 1) {
      throw new \BgaVisibleSystemException('Unexpected card count.');
    }

    $world->moveCardToHand($card, $seat);
    $world->discardCard($other_cards[0]);
  }
}

// "Take one of the top 2 cards from the discard.  (If there are less than 2 cards, add the top card from the deck.)"
class CryptLocation extends Location
{
  const LOCATION_ID = 'location:crypt';
  const SET_ID = SET_BASE;

  public function getValidTargets(World $world)
  {
    // XXX: top 2 of discard; if <2 cards, add top card of deck
    throw new \feException('XXX: no impl');
  }

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToHand($this->getParameterCard($world, 0, $this->getValidTargets($world)), $seat);
  }
}

// "Move one of your other effort from here to any other location."
class DocksLocation extends Location
{
  const LOCATION_ID = 'location:docks';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    if ($this->effortPileForSeat($seat)->qty() <= 1) {
      // If there is only one effort here, it's the one we just placed, and we can never move that one.
      return;
    }

    $loc = $this->getParameterLocation(
      $world,
      0,
      array_values(
        array_filter(function ($loc) use ($this, $world) {
          return $loc->id() != $this->id();
        })
      ),
      $world->getAllLocations()
    );

    $world->moveEffort($loc->effortPileForSeat($seat), $this);
  }
}

// "View both cards here and take 1.  (Replace the missing card face-down.)"
class LibraryLocation extends Location
{
  const LOCATION_ID = 'location:library';
  const SET_ID = SET_BASE;

  const CARDS_FACE_DOWN = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToHand($this->getParameterCardAtLocation($world, 0, $this), $seat);
  }
}

// "Discard a card from your hand, then take both cards here."
class MarketLocation extends Location
{
  const LOCATION_ID = 'location:market';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $world->discardCard($this->getParameterCardInHand($world, 0));

    foreach ($this->cards($world) as $card) {
      $world->moveCardToHand($card, $seat);
    }
  }
}

// "Move another player's effort from any other location to here."
class PrisonLocation extends Location
{
  const LOCATION_ID = 'location:prison';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function getValidTargets(World $world)
  {
    return array_values(
      array_filter(function ($pile) use ($this) {
        return $pile->qty() > 0 &&
          $pile->location()->id() != $this->id() &&
          $pile->seat()->id() != $world->activeSeat()->id();
      }, $world->allEffortPiles())
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveEffort($this->getParameterEffortPile($world, 0, $this->getValidTargets($world)), $this);
  }
}

// "Discard a card at another location."
class RiverLocation extends Location
{
  const LOCATION_ID = 'location:river';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    $cards = [];
    foreach ($world->locations() as $loc) {
      if ($loc->id() != $this->id()) {
        $cards = array_merge($cards, $loc->cards($world));
      }
    }

    $world->discardCard($this->getParameterCard($world, 0, $cards));
  }
}

// "Discard a card from your hand.  Then, take the top 2 cards from the deck."
class TempleLocation extends Location
{
  const LOCATION_ID = 'location:temple';
  const SET_ID = SET_BASE;

  public function onVisited(World $world, Seat $seat)
  {
    $world->discardCard($this->getParameterCardInHand($world, 0));

    for ($i = 0; $i < 2; ++$i) {
      $world->drawCardToHand($seat);
    }
  }
}

// "Move another player's effort from here to any other location."
class TunnelsLocation extends Location
{
  const LOCATION_ID = 'location:tunnels';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function getValidSourcePiles(World $world)
  {
    $piles = [];
    foreach ($this->effortPiles($world) as $seatId => $pile) {
      if ($pile->seat()->id() != $world->activeSeat()->id() && $pile->qty() > 0) {
        $piles[] = $pile;
      }
    }
    return $piles;
  }

  public function getValidDestinationLocations(World $world)
  {
    return array_values(
      array_filter(function ($loc) {
        return $loc->id() != $this->id();
      }, $world->locations())
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $src_pile = $this->getParameterEffortPile($world, 0, $this->getValidSourcePiles($world));
    $dst_loc = $this->getParameterLocation($world, 1, $this->getValidDestinationLocations($world));
    $world->moveEffort($src_pile, $dst_loc);
  }
}

// (No effect.)
class WastelandLocation extends Location
{
  const LOCATION_ID = 'location:wasteland';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    // No effect.
  }
}

// "Move another player's effort from here to an adjacent location OR from an adjacent location to here."
class DungeonLocation extends Location
{
  const LOCATION_ID = 'location:dungeon';
  const SET_ID = SET_ALTERED;

  const CARDS_FACE_UP = 1;

  public function getValidTargets(World $world)
  {
    // XXX: borked
    return array_values(
      array_filter(function ($pile) use ($this) {
        return $pile->qty() > 0 && $pile->seat()->id() != $world->activeSeat()->id();
      }, $world->allEffortPiles())
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    // XXX: borked
    $world->moveEffort($this->getParameterEffortPile($world, 0, $this->getValidTargets($world)), $this);
  }
}

// "Take the card and action of an adjacent location."
class GardenLocation extends Location
{
  const LOCATION_ID = 'location:garden';
  const SET_ID = SET_ALTERED;

  public function onVisited(World $world, Seat $seat)
  {
    $other_loc = $this->getParameterLocation($world, 0, $this->adjacentLocations($world));
    $other_loc->onVisited($world, $seat);
  }
}

// class LairLocation extends Location -- exclusive Dragon mini-expansion

// "Take 1 card from here, then flip the other."
//
// XXX: UI input -- pick card from group
class ObservatoryLocation extends Location
{
  const LOCATION_ID = 'location:observatory';
  const SET_ID = SET_ALTERED;

  const CARDS_FACE_UP = 1;
  const CARDS_FACE_DOWN = 1;

  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: not implemented');
  }
}

// "Swap any other effort here with an effort at any other location."
//
// XXX: UI input -- pick effort here AND pick setloc/effort
class PortalLocation extends Location
{
  const LOCATION_ID = 'location:portal';
  const SET_ID = SET_ALTERED;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: not implemented');
  }
}

// "Move one of your other effort from here to an adjacent location OR from an adjacent location to here."
//
// XXX: UI input -- pick setloc
class StablesLocation extends Location
{
  const LOCATION_ID = 'location:stables';
  const SET_ID = SET_ALTERED;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: not implemented');
  }
}

// "Move any other effort from here to any other location."
class ForestLocation extends Location
{
  const LOCATION_ID = 'location:forest';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    $valid_targets = array_values(
      array_filter(function ($pile) use ($this, $seat, $world) {
        // If there is only of the active seat's effort here, it's the one we just placed, and we can never move that one.
        // For other seats, any effort is fine.
        return $pile->qty() >= ($pile->seat()->id() == $seat->id() ? 2 : 1);
      }, $this->effortPiles($world))
    );

    $world->moveEffort($this->getParameterEffortPile($world, 0, $valid_targets), $this);
  }
}

// "If an attribute is filled on this Location, the threat here gains an additional weakness to cards of that
// attribute."
//
// XXX: complication -- effort here is in a particular sublocation
class LaboratoryLocation extends Location
{
  const LOCATION_ID = 'location:laboratory';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: not implemented');
  }
}

// "Effort cannot be moved to or from this location."
//
// XXX: complication -- this needs to affect all moves
class LabyrinthLocation extends Location
{
  const LOCATION_ID = 'location:labyrinth';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: not implemented');
  }
}

// "Move any other effort from any other location to here."
//
// XXX: UI input -- pick setloc and effort type
class CabinLocation extends Location
{
  const LOCATION_ID = 'location:cabin';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 1;

  // Returns a list of effort piles.
  public function getValidTargets(World $world)
  {
    return array_values(
      array_filter(function ($pile) use ($this) {
        return $pile->qty() > 0 &&
          $pile->location()->id() != $this->id() &&
          $pile->seat()->id() != $world->activeSeat()->id();
      }, $world->allEffortPiles())
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveEffort($this->getParameterEffortPile($world, 0, $this->getValidTargets($world)), $this);
  }
}

// "Take both cards here and replace one with a card from your hand."
class CaravanLocation extends Location
{
  const LOCATION_ID = 'location:hunted';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToLocation($this->getParameterCardInHand($world, 0), $this);

    foreach ($this->cards($world) as $card) {
      $world->moveCardToHand($card, $world->activeSeat());
    }
  }
}

// class TavernLocation extends Location -- KS exclusive

// class TowerLocation extends Location -- KS exclusive

// class TundraLocation extends Location -- KS exclusive

// class VoidLocation extends Location -- KS exclusive

// class UnderworldLocation extends Location -- KS exclusive

// class AlleyLocation extends Location -- KS exclusive
