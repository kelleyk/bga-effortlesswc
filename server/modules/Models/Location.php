<?php declare(strict_types=1);

namespace EffortlessWC\Models;

require_once 'Seat.php';

use EffortlessWC\World;
use EffortlessWC\Models\Seat;

abstract class Location extends \WcLib\CardBase
{
  use \EffortlessWC\Util\ParameterInput;

  const CARD_TYPE_GROUP = 'location';

  public static function getById(World $world, int $id): ?Location
  {
    return $world->table()->locationDeck->get($id);
  }

  public static function mustGetById(World $world, int $id): Location
  {
    $location = self::getById($world, $id);
    if ($location === null) {
      throw new \BgaVisibleSystemException('Could not find location with id=' . $id);
    }
    return $location;
  }

  /**
    @return Location[]
   */
  public static function getAll(World $world)
  {
    return $world->table()->locationDeck->getAll(['SETLOC']);
  }

  // This is meant to be overridden by subclasses; but subclasses sometimes need to change its signature, which is why
  // it's not on `CardBase`.
  //
  // N.B.: This is a `string[]` because MySQL returns every column as a string, regardless of the column's actual type.
  // (XXX: Is this true for NULL values as well?)
  //
  // XXX: We really just want to say "this must return an instance of `get_called_class()` or null"; it should be
  // possible to do that without the template parameter.
  /**
    @param string[]|null $row
    @return Location|null
  */
  public static function fromRow(string $CardT, $deck, $row)
  {
    return self::fromRowBase($CardT, $deck, $row);
  }

  public function renderForClient(World $world): array
  {
    return array_merge(parent::renderForClientBase(/*visible=*/ true), [
      'effort' => $world->table()->getEffortBySeat($this->locationArg()),
    ]);
  }

  // /**
  //   @param string[]|null $row
  //   @return Location
  // */
  // public static function fromRow(World $world, $row): Location
  // {
  //   if ($row === null) {
  //     throw new \BgaVisibleSystemException('Location::fromRow(): got null $row');
  //   }

  //   $loc = self::fromRowBase(Location::class, $row);
  //   return $loc;
  // }

  // Returns the cards that are in play at this location.
  public function cards(World $world)
  {
    return $world->table()->mainDeck->getAll(['SETLOC'], $this->locationArg());
  }

  public function effortPileForSeat(World $world, Seat $seat): EffortPile
  {
    throw new \feException('XXX: no impl: effortPileForSeat');
  }

  // XXX: returns {seatId: EffortPile}  ... or should it return SeatPile[]?
  public function effortPiles(World $world)
  {
    throw new \feException('XXX: no impl: effortPiles');
  }

  // Returns `Location[]`.
  public function adjacentLocations(World $world)
  {
    throw new \feException('XXX: no impl: adjacentLocations');
  }

  public function isAdjacentTo(World $world, Location $other)
  {
    throw new \feException('XXX: no impl: isAdjacentTo');
  }

  // XXX: Should we remove $seat and just use $this->activeSeat() instead?
  public function onVisited(World $world, Seat $seat)
  {
    throw new \feException('XXX: no impl: onVisited');
  }

  public function cardsFaceUp(): int
  {
    $rc = new \ReflectionClass(get_called_class());
    /** @phan-suppress-next-line PhanUndeclaredConstantOfClass */
    return $rc->hasConstant('CARDS_FACE_UP') ? get_called_class()::CARDS_FACE_UP : 0;
  }

  public function cardsFaceDown(): int
  {
    $rc = new \ReflectionClass(get_called_class());
    /** @phan-suppress-next-line PhanUndeclaredConstantOfClass */
    return $rc->hasConstant('CARDS_FACE_DOWN') ? get_called_class()::CARDS_FACE_DOWN : 0;
  }

  public function locationArg(): int
  {
    return $this->sublocationIndex();
  }
}

// XXX: An "effort pile" is a (setloc, seat) pair.

class CaveLocation extends Location
{
  const CARD_TYPE = 'location:cave';
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
  const CARD_TYPE = 'location:city';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToLocation($this->getParameterCardInHand($world, $seat), $this);

    foreach ($this->cards($world) as $card) {
      $world->moveCardToHand($card, $world->activeSeat());
    }
  }
}

// "Take 1 card from here and discard the other."
class ColiseumLocation extends Location
{
  const CARD_TYPE = 'location:coliseum';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $selected_card = $this->getParameterCardAtLocation($world, $this);

    $other_cards = array_values(
      array_filter($this->cards($world), function ($card) use ($selected_card) {
        return $card->id() != $selected_card->id();
      })
    );
    if (count($other_cards) != 1) {
      throw new \BgaVisibleSystemException('Unexpected card count.');
    }

    $world->moveCardToHand($selected_card, $seat);
    $world->discardCard($other_cards[0]);
  }
}

// "Take one of the top 2 cards from the discard.  (If there are less than 2 cards, add the top card from the deck.)"
class CryptLocation extends Location
{
  const CARD_TYPE = 'location:crypt';
  const SET_ID = SET_BASE;

  public function getValidTargets(World $world)
  {
    // XXX: top 2 of discard; if <2 cards, add top card of deck
    throw new \feException('XXX: no impl');
  }

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToHand($this->getParameterCard($world, $this->getValidTargets($world)), $seat);
  }
}

// "Move one of your other effort from here to any other location."
class DocksLocation extends Location
{
  const CARD_TYPE = 'location:docks';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    if ($this->effortPileForSeat($world, $seat)->qty() <= 1) {
      // If there is only one effort here, it's the one we just placed, and we can never move that one.
      return;
    }

    $loc = $this->getParameterLocation(
      $world,
      array_values(
        array_filter($world->locations(), function ($loc) {
          return $loc->id() != $this->id();
        })
      )
    );

    $world->moveEffort($loc->effortPileForSeat($world, $seat), $this->effortPileForSeat($world, $seat));
  }
}

// "View both cards here and take 1.  (Replace the missing card face-down.)"
class LibraryLocation extends Location
{
  const CARD_TYPE = 'location:library';
  const SET_ID = SET_BASE;

  const CARDS_FACE_DOWN = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToHand($this->getParameterCardAtLocation($world, $this), $seat);
  }
}

// "Discard a card from your hand, then take both cards here."
class MarketLocation extends Location
{
  const CARD_TYPE = 'location:market';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $world->discardCard($this->getParameterCardInHand($world, $seat));

    foreach ($this->cards($world) as $card) {
      $world->moveCardToHand($card, $seat);
    }
  }
}

// "Move another player's effort from any other location to here."
class PrisonLocation extends Location
{
  const CARD_TYPE = 'location:prison';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function getValidTargets(World $world)
  {
    return array_values(
      array_filter($world->allEffortPiles(), function ($pile) use ($world) {
        return $pile->qty() > 0 &&
          $pile->location()->id() != $this->id() &&
          $pile->seat()->id() != $world->activeSeat()->id();
      })
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $pile = $this->getParameterEffortPile($world, $this->getValidTargets($world));
    $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat()));
  }
}

// "Discard a card at another location."
class RiverLocation extends Location
{
  const CARD_TYPE = 'location:river';
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

    $world->discardCard($this->getParameterCard($world, $cards));
  }
}

// "Discard a card from your hand.  Then, take the top 2 cards from the deck."
class TempleLocation extends Location
{
  const CARD_TYPE = 'location:temple';
  const SET_ID = SET_BASE;

  public function onVisited(World $world, Seat $seat)
  {
    $world->discardCard($this->getParameterCardInHand($world, $seat));

    for ($i = 0; $i < 2; ++$i) {
      $world->drawCardToHand($seat);
    }
  }
}

// "Move another player's effort from here to any other location."
class TunnelsLocation extends Location
{
  const CARD_TYPE = 'location:tunnels';
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
      array_filter($world->locations(), function ($loc) {
        return $loc->id() != $this->id();
      })
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $src_pile = $this->getParameterEffortPile($world, $this->getValidSourcePiles($world));
    $dst_loc = $this->getParameterLocation($world, $this->getValidDestinationLocations($world));
    $world->moveEffort($src_pile, $dst_loc->effortPileForSeat($world, $src_pile->seat()));
  }
}

// (No effect.)
class WastelandLocation extends Location
{
  const CARD_TYPE = 'location:wasteland';
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
  const CARD_TYPE = 'location:dungeon';
  const SET_ID = SET_ALTERED;

  const CARDS_FACE_UP = 1;

  public function getValidTargets(World $world)
  {
    return array_values(
      array_filter($world->allEffortPiles(), function ($pile) use ($world) {
        return $pile->qty() > 0 && $pile->seat()->id() != $world->activeSeat()->id();
      })
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $pile = $this->getParameterEffortPile($world, $this->getValidTargets($world));
    $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat()));
  }
}

// "Take the card and action of an adjacent location."
class GardenLocation extends Location
{
  const CARD_TYPE = 'location:garden';
  const SET_ID = SET_ALTERED;

  public function onVisited(World $world, Seat $seat)
  {
    $other_loc = $this->getParameterLocation($world, $this->adjacentLocations($world));
    $other_loc->onVisited($world, $seat);
  }
}

// class LairLocation extends Location -- exclusive Dragon mini-expansion

// "Take 1 card from here, then flip the other."
//
// XXX: UI input -- pick card from group
class ObservatoryLocation extends Location
{
  const CARD_TYPE = 'location:observatory';
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
  const CARD_TYPE = 'location:portal';
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
  const CARD_TYPE = 'location:stables';
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
  const CARD_TYPE = 'location:forest';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 1;

  public function onVisited(World $world, Seat $seat)
  {
    $valid_target_piles = array_values(
      array_filter($this->effortPiles($world), function ($pile) use ($seat, $world) {
        // If there is only of the active seat's effort here, it's the one we just placed, and we can never move that one.
        // For other seats, any effort is fine.
        return $pile->qty() >= ($pile->seat()->id() == $seat->id() ? 2 : 1);
      })
    );

    $valid_target_locations = array_values(
      array_filter($world->locations(), function ($loc) {
        return $loc->id() != $this->id();
      })
    );

    $pile = $this->getParameterEffortPile($world, $valid_target_piles);
    $dst = $this->getParameterLocation($world, $valid_target_locations);
    $world->moveEffort($pile, $dst->effortPileForSeat($world, $pile->seat()));
  }
}

// "If an attribute is filled on this Location, the threat here gains an additional weakness to cards of that
// attribute."
//
// XXX: complication -- effort here is in a particular sublocation
class LaboratoryLocation extends Location
{
  const CARD_TYPE = 'location:laboratory';
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
  const CARD_TYPE = 'location:labyrinth';
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
  const CARD_TYPE = 'location:cabin';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 1;

  // Returns a list of effort piles.
  public function getValidTargets(World $world)
  {
    return array_values(
      array_filter($world->allEffortPiles(), function ($pile) use ($world) {
        return $pile->qty() > 0 &&
          $pile->location()->id() != $this->id() &&
          $pile->seat()->id() != $world->activeSeat()->id();
      })
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $pile = $this->getParameterEffortPile($world, $this->getValidTargets($world));
    $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat()));
  }
}

// "Take both cards here and replace one with a card from your hand."
class CaravanLocation extends Location
{
  const CARD_TYPE = 'location:caravan';
  const SET_ID = SET_HUNTED;

  const CARDS_FACE_UP = 2;

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToLocation($this->getParameterCardInHand($world, $seat), $this);

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
