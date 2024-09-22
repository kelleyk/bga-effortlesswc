<?php declare(strict_types=1);

namespace Effortless\Test;

require_once '/src/localarena/module/test/IntegrationTestCase.php';

// // XXX: Necessary for Phan until we sort out path structure once and for all.
// if (!defined('LOCALARENA_GAME_PATH')) {
//   define('LOCALARENA_GAME_PATH', '/src/game/');
// }

require_once LOCALARENA_GAME_PATH . 'effortless/modules/php/constants.inc.php';
require_once LOCALARENA_GAME_PATH . 'effortless/modules/php/WcLib/WcDeck.php';

use LocalArena\TableParams;

// use LocalArena\Test\PlayerPeer;

// use WcLib\WcDeck;

// use Effortless\Models\Card;
// use Effortless\Models\Player;
use Effortless\Models\EffortPile;
use Effortless\Models\Location;
use Effortless\Models\Seat;
use Effortless\Models\Setting;

// function array_value_first($arr)
// {
//   $k = array_key_first($arr);
//   return $arr[$k];
// }

class IntegrationTestCase extends \LocalArena\Test\IntegrationTestCase
{
  const LOCALARENA_GAME_NAME = 'effortless';

  function setupCleanState(): void
  {
    // $this->assertEquals(1, 2);

    $params = new TableParams();
    $params->playerCount = 1;
    $this->initTable($params);

    // XXX: BurgleBrosTwo has a good example of things we might want to wipe clean here.

    echo "\n" . '** Done with `setupCleanState()`; starting actual test case.' . "\n\n";
  }

  public function setlocByPos(int $pos): SetlocPeer
  {
    return new SetlocPeer($this, $pos);
  }

  public function seat(int $id): SeatPeer
  {
    return new SeatPeer($this, $id);
  }

  public function activeSeat(): SeatPeer
  {
    return $this->seat($this->activeSeatId());
  }

  public function activeSeatId(): int
  {
    return $this->table()->world()->activeSeat()->id();
  }
}

class PlayerPeer extends \LocalArena\Test\PlayerPeer
{
  public function __construct(IntegrationTestCase $itc, string $player_id)
  {
    // N.B.: In Effortless, we're experimenting with having peers that only store primary keys, so that they don't need
    // to be `refresh()`ed like the originals in Burgle Bros 2 do.  The `PlayerPeer` in LocalArena still uses the
    // original style, which is why we need to go fetch the row like this.
    $row = $itc->table()->rawGetPlayerById($player_id);
    parent::__construct($itc, $row);
  }
}

// XXX: Some version of this should be promoted to WcLib, since the "seat" concept itself now has been.
//
// XXX: This is copy-pasted from HandPeer in Shipwreck's test suite.  We need to support seats that are _not_ associated
// with players as well.
class SeatPeer
{
  private IntegrationTestCase $itc_;

  private int $id_;

  private function table()
  {
    return $this->itc_->table();
  }

  public function __construct(IntegrationTestCase $itc, int $id)
  {
    $this->itc_ = $itc;
    $this->id_ = $id;
  }

  public function id(): int
  {
    return $this->id_;
  }

  // XXX: We need a better name for this.
  public function implObj(): Seat
  {
    return Seat::mustGetById($this->table()->world(), $this->id());
  }

  // XXX: Typing issue: this LocalArena factory returns the
  // LocalArena PlayerPeer, whereas we want the game-local one.
  // There'll be even more trouble if/when we have game-specific
  // columns in the players table.
  //
  // For now, removing the annotation here makes this "just work"
  // through the "magic" of PHP's type system.
  //
  // N.B.: This can return null, because not every seat in Effortless is associated with a player.
  public function player()
  {
    $player_id = $this->implObj()->player_id();
    if ($player_id === null) {
      return null;
    }
    return new PlayerPeer($this->itc_, $player_id);
  }

  public function actVisit(SetlocPeer $setloc): void
  {
    $this->player()->act('actSelectInput', [
      'selection' => [
        'inputType' => 'inputtype:location', // XXX: This is `INPUTTYPE_LOCATION`.
        'value' => $setloc->locationId(),
      ],
    ]);
  }
}

class SetlocPeer
{
  private IntegrationTestCase $itc_;

  private int $pos_;

  public function __construct(IntegrationTestCase $itc, int $pos)
  {
    $this->itc_ = $itc;
    $this->pos_ = $pos;
  }

  private function table()
  {
    return $this->itc_->table();
  }

  public function pos(): int
  {
    return $this->pos_;
  }

  // Moves a $location in play (with sublocation SETLOC) back to the deck.  Any cards at that location are discarded.
  //
  // This isn't intended to be used directly; see `setLocation()`.
  private function unplaceLocation(LocationPeer $location): void
  {
    $world = $this->table()->world();
    $deck = $this->table()->locationDeck;

    if ($location->sublocation() != 'SETLOC') {
      throw new \BgaVisibleSystemException('Cannot `unplaceLocation()` a location that is not in play.');
    }

    // Discard all cards.
    foreach ($location->implObj()->cards($world) as $card) {
      $world->discardCard($card);
    }

    $deck->placeOnBottom($location, 'DECK', null);
  }

  // Moves a $location that is in the deck or discard pile (with sublocation DECK or DISCARD) into play at $pos.  There
  // must not be any card already in play at (SETLOC, $pos).  The location is refilled with cards from the deck.
  //
  // This isn't intended to be used directly; see `setLocation()`.
  //
  // N.B.: This does *not* update effort-piles.  Doing that requires knowing the index of the location that previously
  // occupied $pos.
  private function placeLocation(LocationPeer $location, int $pos): void
  {
    $world = $this->table()->world();
    $deck = $this->table()->locationDeck;

    if ($location->sublocation() != 'DECK' && $location->sublocation() != 'DISCARD') {
      throw new \BgaVisibleSystemException(
        'Cannot `placeLocation()` a location that is not in either the deck or discard pile.'
      );
    }

    $existing_location = $deck->getUniqueByLocation('SETLOC', $pos);
    if ($existing_location !== null) {
      throw new \BgaVisibleSystemException(
        'Cannot `placeLocation()` a location at position ' .
          $pos .
          '; ' .
          $existing_location->type() .
          ' is already there.'
      );
    }

    $deck->placeOnTop($location, 'SETLOC', $pos);

    // Refill with cards from the deck.
    $world->fillCards($location->implObj());
  }

  // XXX: We also need to change the card(s) that are at each new location so that they match the number and
  // face-up/down state that the new location expects (rather than what the previous location expects).
  public function setLocation(string $card_type): void
  {
    $deck = $this->table()->locationDeck;

    // Get the location that is currently at this position before we start making state changes.
    $prev_location = $this->location();

    $matching_location_implobj = $deck->getUniqueByType(Location::CARD_TYPE_GROUP, $card_type);
    if ($matching_location_implobj === null) {
      throw new \BgaVisibleSystemException('No location found for type: ' . $card_type);
    }
    $matching_location = new LocationPeer($this->itc_, $matching_location_implobj->id());

    // N.B.: This function (and `setSetting()`) need to handle the situation where $card_type is already in play, but at
    // a different `$this->pos_`; otherwise, we'll leave a gap at its previous position.
    if ($matching_location->sublocation() == 'SETLOC') {
      $matching_location_prev_pos = $matching_location->sublocationIndex();
      $this->unplaceLocation($matching_location);
      $this->placeLocation(new LocationPeer($this->itc_, $deck->peekTop()->id()), $matching_location_prev_pos);
    }

    // Put the location currently at `$this->pos_` back in the deck, and place the target location into play in that position.
    $this->unplaceLocation($prev_location);
    $this->placeLocation($matching_location, $this->pos_);

    // Update effort-piles that were tied to the replaced location.  This is necessary because effort-piles are (perhaps
    // unwisely) keyed by location and not by position on the board.  We're going to need to do the same thing in the
    // game itself if we ever implement any of the content that involves replacing setlocs.
    $this->table()->DbQuery(
      'UPDATE `effort_pile` SET `location_id` = ' .
        $matching_location->id() .
        ' WHERE `location_id` = ' .
        $prev_location->id()
    );
  }

  public function location(): LocationPeer
  {
    return new LocationPeer($this->itc_, $this->locationId());
  }

  public function locationId(): int
  {
    $location_implobj = $this->table()->locationDeck->getUniqueByLocation('SETLOC', $this->pos_);
    if ($location_implobj === null) {
      throw new \BgaVisibleSystemException('No location found for position: ' . $this->pos_);
    }
    return $location_implobj->id();
  }

  // Moves a $setting in play (with sublocation SETLOC) back to the deck.  Any cards at that setting are discarded.
  //
  // This isn't intended to be used directly; see `setSetting()`.
  private function unplaceSetting(SettingPeer $setting): void
  {
    $world = $this->table()->world();
    $deck = $this->table()->settingDeck;

    if ($setting->sublocation() != 'SETLOC') {
      throw new \BgaVisibleSystemException('Cannot `unplaceSetting()` a setting that is not in play.');
    }

    $deck->placeOnBottom($setting, 'DECK', null);
  }

  // Moves a $setting that is in the deck or discard pile (with sublocation DECK or DISCARD) into play at $pos.  There
  // must not be any card already in play at (SETLOC, $pos).
  //
  // This isn't intended to be used directly; see `setSetting()`.
  private function placeSetting(SettingPeer $setting, int $pos): void
  {
    $world = $this->table()->world();
    $deck = $this->table()->settingDeck;

    if ($setting->sublocation() != 'DECK' && $setting->sublocation() != 'DISCARD') {
      throw new \BgaVisibleSystemException(
        'Cannot `placeSetting()` a setting that is not in either the deck or discard pile.'
      );
    }

    $existing_setting = $deck->getUniqueByLocation('SETLOC', $pos);
    if ($existing_setting !== null) {
      throw new \BgaVisibleSystemException(
        'Cannot `placeSetting()` a setting at position ' .
          $pos .
          '; ' .
          $existing_setting->type() .
          ' is already there.'
      );
    }

    $deck->placeOnTop($setting, 'SETLOC', $pos);
  }

  // XXX: We also need to change the card(s) that are at each new setting so that they match the number and
  // face-up/down state that the new setting expects (rather than what the previous setting expects).
  public function setSetting(string $card_type): void
  {
    $deck = $this->table()->settingDeck;

    // Get the setting that is currently at this position before we start making state changes.
    $prev_setting = $this->setting();

    $matching_setting_implobj = $deck->getUniqueByType(Setting::CARD_TYPE_GROUP, $card_type);
    if ($matching_setting_implobj === null) {
      throw new \BgaVisibleSystemException('No setting found for type: ' . $card_type);
    }
    $matching_setting = new SettingPeer($this->itc_, $matching_setting_implobj->id());

    // N.B.: This function (and `setSetting()`) need to handle the situation where $card_type is already in play, but at
    // a different `$this->pos_`; otherwise, we'll leave a gap at its previous position.
    if ($matching_setting->sublocation() == 'SETLOC') {
      $matching_setting_prev_pos = $matching_setting->sublocationIndex();
      $this->unplaceSetting($matching_setting);
      $this->placeSetting(new SettingPeer($this->itc_, $deck->peekTop()->id()), $matching_setting_prev_pos);
    }

    // Put the setting currently at `$this->pos_` back in the deck, and place the target setting into play in that position.
    $this->unplaceSetting($prev_setting);
    $this->placeSetting($matching_setting, $this->pos_);
  }

  public function settingId(): int
  {
    $setting_implobj = $this->table()->settingDeck->getUniqueByLocation('SETLOC', $this->pos_);
    if ($setting_implobj === null) {
      throw new \BgaVisibleSystemException('No setting found for position: ' . $this->pos_);
    }
    return $setting_implobj->id();
  }

  public function setting(): SettingPeer
  {
    return new SettingPeer($this->itc_, $this->settingId());
  }

  public function effortPileBySeat(SeatPeer $seat): EffortPilePeer
  {
    $pile_implobj = $this->location()->implObj()->effortPileForSeat($this->table()->world(), $seat->implObj());
    return new EffortPilePeer($this->itc_, $pile_implobj->id());
  }
}

// XXX: A lot of this could be replaced with a `CardBasePeer` in `WcLib`.
class LocationPeer
{
  private IntegrationTestCase $itc_;

  private int $id_;

  private function table()
  {
    return $this->itc_->table();
  }

  public function __construct(IntegrationTestCase $itc, int $id)
  {
    $this->itc_ = $itc;
    $this->id_ = $id;
  }

  // XXX: We need a better name for this.
  public function implObj(): Location
  {
    return Location::mustGetById($this->table()->world(), $this->id());
  }

  public function id(): int
  {
    return $this->id_;
  }

  // // XXX: This is called `type_id()` on the `implObj()`.
  // public function cardTypeId(): int
  // {
  //   return $this->card_type_id_;
  // }

  public function location(): string
  {
    return $this->implObj()->location();
  }

  public function locationArg(): int
  {
    return $this->implObj()->locationArg();
  }

  public function sublocation(): string
  {
    return $this->implObj()->sublocation();
  }

  public function sublocationIndex(): int
  {
    return $this->implObj()->sublocationIndex();
  }
}

class EffortPilePeer
{
  private IntegrationTestCase $itc_;

  private int $id_;

  private function table()
  {
    return $this->itc_->table();
  }

  public function __construct(IntegrationTestCase $itc, int $id)
  {
    $this->itc_ = $itc;
    $this->id_ = $id;
  }

  public function implObj(): EffortPile
  {
    return EffortPile::mustGetById($this->table()->world(), $this->id());
  }

  public function id(): int
  {
    return $this->id_;
  }

  public function qty(): int
  {
    return $this->implObj()->qty();
  }
}

class SettingPeer
{
  private IntegrationTestCase $itc_;

  private int $id_;

  private function table()
  {
    return $this->itc_->table();
  }

  public function __construct($itc, int $id)
  {
    $this->itc_ = $itc;
    $this->id_ = $id;
  }

  public function implObj(): Setting
  {
    return Setting::mustGetById($this->table()->world(), $this->id());
  }

  public function id(): int
  {
    return $this->id_;
  }

  public function location(): string
  {
    return $this->implObj()->location();
  }

  public function locationArg(): int
  {
    return $this->implObj()->locationArg();
  }

  public function sublocation(): string
  {
    return $this->implObj()->sublocation();
  }

  public function sublocationIndex(): int
  {
    return $this->implObj()->sublocationIndex();
  }
}
