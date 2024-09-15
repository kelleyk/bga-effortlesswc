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
use Effortless\Models\Location;
// use Effortless\Models\Player;
// use Effortless\Models\Seat;
// use Effortless\Models\Setting;

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
    // XXX:
    throw new \BgaUserException('no impl: setlocbypos');
  }

  public function activeSeat(): SeatPeer
  {
    // XXX:
    throw new \BgaUserException('no impl: activeseat');
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
  private string $player_id_;

  private function table()
  {
    return $this->itc_->table();
  }

  public function __construct($itc, $row)
  {
    if ($row === null) {
      throw new \BgaUserException('$row is null');
    }

    $this->itc_ = $itc;

    $this->id_ = intval($row['id']);
    $this->player_id_ = $row['player_id'];
  }

  public function id(): int
  {
    return $this->id_;
  }

  // XXX: Typing issue: this LocalArena factory returns the
  // LocalArena PlayerPeer, whereas we want the game-local one.
  // There'll be even more trouble if/when we have game-specific
  // columns in the players table.
  //
  // For now, removing the annotation here makes this "just work"
  // through the "magic" of PHP's type system.
  public function player()
  {
    // : PlayerPeer
    return $this->itc_->playerById($this->player_id_);
  }

  public function actVisit(SetlocPeer $setloc): void
  {
    throw new \BgaUserException('XXX: no impl: actVisit');
  }
}

class SetlocPeer
{
  private IntegrationTestCase $itc_;

  private int $pos_;
  private LocationPeer $location_;
  private SettingPeer $setting_;

  private function table()
  {
    return $this->itc_->table();
  }

  // XXX: How *should* we construct a SetlocPeer?  Probably need to take a $pos and get the appropriate set/loc peers.
  //
  // public function __construct($itc, $row)
  // {
  //   if ($row === null) {
  //     throw new \BgaUserException('$row is null');
  //   }
  //
  //   $this->itc_ = $itc;
  //
  //   // XXX:
  //   $this->id_ = intval($row['id']);
  // }

  public function pos(): int
  {
    return $this->pos_;
  }

  public function setLocation(string $cardType): void
  {
    throw new \BgaUserException('no impl: setlocation');
  }

  public function location(): LocationPeer
  {
    return $this->location_;
  }

  public function setSetting(string $cardType): void
  {
    throw new \BgaUserException('no impl: setsetting');
  }

  public function setting(): SettingPeer
  {
    return $this->setting_;
  }

  public function effortPileBySeat(SeatPeer $seat): EffortPilePeer
  {
    throw new \BgaUserException('no impl: effortpilebyseat');
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

  public function __construct($itc, $row)
  {
    // if ($row === null) {
    //   throw new \BgaUserException('$row is null');
    // }

    // if ($row instanceof ArcanaCard) {
    //   $row = $itc->table()->getCardById($row->id());
    // }

    // $this->itc_ = $itc;

    // $this->id_ = intval($row['card_id']);
    // $this->card_type_id_ = intval($row['card_type_Argo']);
  }

  // XXX: We need a better name for this.
  public function implObj(): Location
  {
    throw new \BgaUserException('XXX: no impl: locationPeer::implObj()');
    // return ArcanaCard::getById($this->table()->world(), $this->id());
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
  public function qty(): int
  {
    throw new \BgaUserException('no impl: EffortPilePeer::qty()');
  }
}

class SettingPeer
{
}
