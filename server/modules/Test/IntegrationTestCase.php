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

  public function seat(int $id): SeatPeer {
    return new SeatPeer($this, $id);
  }

  public function activeSeat(): SeatPeer
  {
    return $this->seat($this->activeSeatId());
  }

  public function activeSeatId(): int {
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
  public function implObj(): Seat {
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
        'inputType' => 'inputtype:location',  // XXX: This is `INPUTTYPE_LOCATION`.
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

  public function setLocation(string $card_type): void
  {
    $deck = $this->table()->locationDeck;

    // Put the current location back in the deck.
    $prev_location_id = $this->locationId();
    $deck->placeOnBottom($deck->mustGet($prev_location_id), 'DECK', null);

    // Find the given location, and put it in `$this->pos_`.
    $matching_location = $deck->getUniqueByType(Location::CARD_TYPE_GROUP, $card_type);
    if ($matching_location === null) {
      throw new \BgaVisibleSystemException('No location found for type: ' . $card_type);
    }
    $next_location_id = $matching_location->id();
    $deck->placeOnTop($matching_location, 'SETLOC', $this->pos_);

    // Update effort-piles that were tied to the replaced location.  This is necessary because effort-piles are (perhaps
    // unwisely) keyed by location and not by position on the board.  We're going to need to do the same thing in the
    // game itself if we ever implement any of the content that involves replacing setlocs.
    $this->table()->DbQuery(
      'UPDATE `effort_pile` SET `location_id` = ' . $next_location_id . ' WHERE `location_id` = ' . $prev_location_id,
    );
  }

  public function location(): LocationPeer
  {
    return new LocationPeer($this->itc_, $this->locationId());
  }

  public function locationId(): int {
    $location_implobj = $this->table()->locationDeck->getUniqueByLocation('SETLOC', $this->pos_);
    if ($location_implobj === null) {
      throw new \BgaVisibleSystemException('No location found for position: ' . $this->pos_);
    }
    return $location_implobj->id();
  }

  public function setSetting(string $card_type): void
  {
    $deck = $this->table()->settingDeck;

    // Put the current setting back in the deck.
    $prev_setting = $deck->mustGet($this->settingId());
    $deck->placeOnBottom($prev_setting, 'DECK', null);

    // Find the given setting, and put it in `$this->pos_`.
    $matching_setting = $deck->getUniqueByType(Setting::CARD_TYPE_GROUP, $card_type);
    if ($matching_setting === null) {
      throw new \BgaVisibleSystemException('No setting found for type: ' . $card_type);
    }
    $deck->placeOnTop($matching_setting, 'SETLOC', $this->pos_);
  }

  public function settingId(): int {
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
    $pile_implobj = $this->location()->implObj()->effortPileForSeat(
      $this->table()->world(),
      $seat->implObj(),
    );
    return new EffortPilePeer($this->itc_, $pile_implobj->id());
  }
}

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

  public function implObj(): EffortPile {
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

  public function implObj(): Setting {
    return Setting::mustGetById($this->table()->world(), $this->id());
  }

  public function id(): int
  {
    return $this->id_;
  }
}
