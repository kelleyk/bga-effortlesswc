<?php declare(strict_types=1);

namespace Effortless\Models;

require_once 'Seat.php';
require_once realpath(__DIR__ . '/../StaticDataSetlocs.php');

use Effortless\World;
use Effortless\Models\Seat;
use Effortless\Models\EffortPile;

use Effortless\Util\NoChoicesAvailableException;

// XXX: We need to have a simple test that we actually wind up with one more card than we started with for each location
// type, even e.g. when the location effect cannot be performed.

abstract class Location extends \WcLib\CardBase
{
  use \Effortless\Util\ParameterInput;

  const CARD_TYPE_GROUP = 'location';

  public static function getById(World $world, int $id): ?Location
  {
    return $world->table()->locationDeck->get($id);
  }

  public static function mustGetById(World $world, int $id): Location
  {
    $location = self::getById($world, $id);
    if ($location === null) {
      throw new \WcLib\Exception('Could not find location with id=' . $id);
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
      'effort' => $world->table()->rawGetEffortPilesBySeat($this->locationArg()),
    ]);
  }

  public function renderForNotif(World $world): string
  {
    $setting_type = explode(':', $this->pairedSetting($world)->type())[1];
    $location_type = explode(':', $this->type())[1];
    return 'the <strong>:setting=' . $setting_type . ': :location=' . $location_type . ':</strong>';
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
    $rows_by_id = $world->table()->rawGetEffortPilesBySeat($this->id());
    // throw new \feException(print_r($rows_by_id, true));

    $pile = EffortPile::fromRow($rows_by_id[$seat->id()]);
    if ($pile === null) {
      throw new \BgaVisibleSystemException('Effort pile not found.');
    }
    return $pile;
  }

  // Returns the location in the same position as this setting (that is, the one that is "puzzle pieced" together with
  // it).
  public function pairedSetting(World $world): Setting
  {
    $loc_to_set = $world->table()->getLocToSetMap();
    return Setting::mustGetById($world, $loc_to_set[$this->id()]);
  }

  /** @return EffortPile[] */
  public function effortPiles(World $world)
  {
    $rows_by_id = $world->table()->rawGetEffortPilesBySeat($this->id());

    return array_map(function ($row) {
      return EffortPile::fromRow($row);
    }, array_values($rows_by_id));
  }

  /** @return Location[] */
  public function adjacentLocations(World $world)
  {
    throw new \feException('XXX: no impl: adjacentLocations');
  }

  public function isAdjacentTo(World $world, Location $other): bool
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

  // Take the lone card on the location.  This is the behavior that locations that don't specifically mention something
  // about how to draw a card have.
  public function takeOnlyCard(World $world): void
  {
    $cards = $this->cards($world);
    if (count($cards) != 1) {
      throw new \BgaVisibleSystemException('Location does not have exactly one card.');
    }

    $card = $cards[array_key_first($cards)];
    $world->moveCardToHand($card, $world->activeSeat());
  }

  protected function metadata()
  {
    $type = explode(':', $this->type())[1];
    return LOCATION_METADATA[$type];
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
    // No effects (but we always take the card that's available).
    $this->takeOnlyCard($world);
  }
}

// "Move one of your effort from any other location to here."
class CityLocation extends Location
{
  const CARD_TYPE = 'location:city';
  const SET_ID = SET_BASE;

  const CARDS_FACE_UP = 1;

  public function getValidTargets(World $world)
  {
    return array_values(
      array_filter($world->allEffortPiles(), function ($pile) use ($world) {
        return $pile->qty() > 0 &&
          $pile->seat($world)->id() == $world->activeSeat()->id() &&
          $pile->location($world)->id() != $this->id();
      })
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    $pile = null;
    try {
      $pile = $this->getParameterEffortPile($world, $this->getValidTargets($world), [
        'description' =>
          '${actplayer} must pick one of their effort from another location to move to the :location=city:.',
        'descriptionmyturn' =>
          '${you} must pick one of your effort from another location to move to the :location=city:.',
      ]);
    } catch (NoChoicesAvailableException $e) {
      $world
        ->table()
        ->notifyAllPlayers(
          'message',
          '${seat} does not have any effort at the :location=city:, so none will be moved.',
          [
            'seat' => $world->activeSeat()->renderForNotif($world),
          ]
        );
    }

    if ($pile !== null) {
      $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat($world)));
    }

    $this->takeOnlyCard($world);
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
    $selected_card = $this->getParameterCardAtLocation($world, $this, [
      'description' =>
        '${actplayer} must pick a card from the :location=coliseum: to take; the other will be discarded.',
      'descriptionmyturn' =>
        '${you} must pick a card from the :location=coliseum: to take; the other will be discarded.',
    ]);

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
    // If there are fewer than two cards in the discard pile, we discard the top card of the deck.  (This is per Isaac; )
    $discard_size = count($world->table()->mainDeck->getAll(['DISCARD']));
    $cards_needed = max(0, 2 - $discard_size);
    //throw new \feException('crypt location: cards that need to be discarded: ' . $cards_needed);
    for ($i = 0; $cards_needed; ++$i) {
      $world->table()->mainDeck->drawAndDiscard();
    }

    // Get the top two cards of the discard pile.
    $discarded_cards = $world->table()->mainDeck->getAll(['DISCARD']);
    usort($discarded_cards, function (Card $card_a, Card $card_b) {
      return $card_a->id() - $card_b->id();
    });
    $choices = array_slice($discarded_cards, 0, 2);

    return $choices;
  }

  public function onVisited(World $world, Seat $seat)
  {
    $world->moveCardToHand(
      $this->getParameterCard($world, $this->getValidTargets($world), [
        'description' => '${actplayer} must pick one of the top two cards of the discard pile to take.',
        'descriptionmyturn' => '${you} must pick one of the top two cards of the discard pile to take.',
      ]),
      $seat
    );
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
      $this->takeOnlyCard($world);
      return;
    }

    $loc = $this->getParameterLocation(
      $world,
      array_values(
        array_filter($world->locations(), function ($loc) {
          return $loc->id() != $this->id();
        })
      ),
      [
        'description' =>
          '${actplayer} must pick another location; one of their effort at the :location=docks: will be moved there.',
        'descriptionmyturn' =>
          '${you} must pick another location; one of your effort at the :location=docks: will be moved there.',
      ]
    );

    $world->moveEffort($loc->effortPileForSeat($world, $seat), $this->effortPileForSeat($world, $seat));

    $this->takeOnlyCard($world);
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
    $world->moveCardToHand(
      $this->getParameterCardAtLocation($world, $this, [
        'description' =>
          '${actplayer} must pick one of the two face-down cards at the :location=library: to take; the other will be replaced, face-down.',
        'descriptionmyturn' =>
          '${you} must pick one of the two face-down cards at the :location=library: to take; the other will be replaced, face-down.',
      ]),
      $seat
    );
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
    try {
      $world->discardCard(
        $this->getParameterCardInHand($world, $seat, [
          'description' => '${actplayer} must pick a card from their hand to discard.',
          'descriptionmyturn' => '${you} must pick a card from your hand to discard.',
        ])
      );
    } catch (NoChoicesAvailableException $e) {
      // XXX: We should consider greying this location out as unselectable on the client side.
      throw new \BgaUserException('You cannot visit the Market because you do not have a card to discard.');
    }

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
    $this->takeOnlyCard($world);

    return array_values(
      array_filter($world->allEffortPiles(), function ($pile) use ($world) {
        return $pile->qty() > 0 && $pile->locationId() != $this->id() && $pile->seatId() != $world->activeSeat()->id();
      })
    );
  }

  public function onVisited(World $world, Seat $seat)
  {
    try {
      $pile = $this->getParameterEffortPile($world, $this->getValidTargets($world), [
        'description' =>
          '${actplayer} must pick another player\'s effort pile at a different location; one effort from that pile will be moved to the :location=prison:.',
        'descriptionmyturn' =>
          '${you} must pick another player\'s effort pile at a different location; one effort from that pile will be moved to the :location=prison:.',
      ]);
    } catch (NoChoicesAvailableException $e) {
      $world->table()->notifyAllPlayers('message', 'There is not any effort that can be moved to ${location}.', [
        'location' => $this->renderForNotif($world),
      ]);
      return;
    }

    $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat($world)));

    $this->takeOnlyCard($world);
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

    // XXX: We probably need to handle NoChoicesAvailableException here.
    $world->discardCard(
      $this->getParameterCard($world, $cards, [
        'description' => '${actplayer} must pick a card at a location other than the :location=river: to discard.',
        'descriptionmyturn' => '${you} must pick a card at a location other than the :location=river: to discard.',
      ])
    );

    $this->takeOnlyCard($world);
  }
}

// "Discard a card from your hand.  Then, take the top 2 cards from the deck."
class TempleLocation extends Location
{
  const CARD_TYPE = 'location:temple';
  const SET_ID = SET_BASE;

  public function onVisited(World $world, Seat $seat)
  {
    try {
      $world->discardCard(
        $this->getParameterCardInHand($world, $seat, [
          'description' => '${actplayer} must pick a card from their hand to discard.',
          'descriptionmyturn' => '${you} must pick a card from your hand to discard.',
        ])
      );
    } catch (NoChoicesAvailableException $e) {
      // XXX: We should consider greying this location out as unselectable on the client side.
      throw new \BgaUserException('You cannot visit the Temple because you do not have a card to discard.');
    }

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
    // XXX: This could be `array_values(array_filter())` instead.
    $piles = [];
    foreach ($this->effortPiles($world) as $pile) {
      // throw new \feException(print_r($pile, true));
      if ($pile->seat($world)->id() != $world->activeSeat()->id() && $pile->qty() > 0) {
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
    try {
      $src_pile = $this->getParameterEffortPile($world, $this->getValidSourcePiles($world), [
        'description' =>
          '${actplayer} must pick another player\'s effort pile at the :location=tunnels: to move an effort from.',
        'descriptionmyturn' =>
          '${you} must pick another player\'s effort pile at the :location=tunnels: to move an effort from.',
      ]);
    } catch (NoChoicesAvailableException $e) {
      $world->table()->notifyAllPlayers('message', 'There is not any effort that can be moved from ${location}.', [
        'location' => $this->renderForNotif($world),
      ]);
      return;
    }

    $dst_loc = $this->getParameterLocation($world, $this->getValidDestinationLocations($world), [
      'description' => '${actplayer} must pick a location other than the :location=tunnels: to move that effort to.',
      'descriptionmyturn' => '${you} must pick a location other than the :location=tunnels: to move that effort to.',
    ]);
    $world->moveEffort($src_pile, $dst_loc->effortPileForSeat($world, $src_pile->seat($world)));

    $this->takeOnlyCard($world);
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
    $this->takeOnlyCard($world);
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

  // XXX: I'm not sure that this is correct; we should probably add some testing.
  public function onVisited(World $world, Seat $seat)
  {
    try {
      $pile = $this->getParameterEffortPile($world, $this->getValidTargets($world));
    } catch (NoChoicesAvailableException $e) {
      $world->table()->notifyAllPlayers('message', 'There is not any effort that can be moved to ${location}.', [
        'location' => $this->renderForNotif($world),
      ]);
      return;
    }

    $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat($world)));

    $this->takeOnlyCard($world);
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
    $world->moveEffort($pile, $dst->effortPileForSeat($world, $pile->seat($world)));

    $this->takeOnlyCard($world);
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
    $world->moveEffort($pile, $this->effortPileForSeat($world, $pile->seat($world)));
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

    // XXX: Won't this, in effect, pick back up the card we've just put down?
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
