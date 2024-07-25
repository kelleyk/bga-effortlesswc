<?php declare(strict_types=1);

namespace Effortless\Models;

require_once 'EffortPile.php';

use Effortless\World;
use Effortless\Models\EffortPile;

class Seat extends \WcLib\SeatBase
{
  protected int $turn_order_;

  public static function getById(World $world, int $id): ?Seat
  {
    return self::fromRow($world->table()->rawGetSeatById($id));
  }

  public static function mustGetById(World $world, int $id): Seat
  {
    $seat = self::getById($world, $id);
    if ($seat === null) {
      throw new \WcLib\Exception('Could not find seat with id=' . $id);
    }
    return $seat;
  }

  /**
    @param string[] $row
    @return ?Seat
  */
  public static function fromRow($row)
  {
    $that = parent::fromRowBase(Seat::class, $row);

    if ($that !== null) {
      $that->turn_order_ = intval($row['turn_order']);
    }

    return $that;
  }

  public function reserveEffort(World $world): EffortPile
  {
    $pile = EffortPile::fromRow($world->table()->rawGetReserveEffortPile($this->id()));
    if ($pile === null) {
      throw new \BgaVisibleSystemException('Effort pile not found.');
    }
    return $pile;
  }

  function turn_order(): int
  {
    return $this->turn_order_;
  }

  /**
    @return Seat[]
   */
  public static function getAll(World $world)
  {
    return array_map(function ($row) {
      return Seat::fromRow($row);
    }, $world->table()->rawGetSeats());
  }

  public function renderForClient(World $world)
  {
    return array_merge(parent::renderForClientBase(), [
      // XXX: I'm not sure that we want to render `reserveEffort` this way.
      'reserveEffort' => $this->reserveEffort($world)->qty(),
      'colorName' => $this->color_name(),
    ]);
  }

  public function renderForNotif(World $world): string
  {
    return '<strong class="ewc_playercolor_fg_' . $this->color_name() . '">' . $this->seat_label() . '</strong>';
  }

  function inputPlayer(World $world): Player
  {
    $player_id = $this->player_id();
    if ($player_id === null) {
      $player_id = '' . $world->table()->getGameStateInt(GAMESTATE_INT_DECIDING_PLAYER);
    }
    return Player::mustGetById($world, $player_id);
  }

  function color_name(): string
  {
    switch ($this->seat_color()) {
      case '001489':
        return 'blue';
      case 'ff5fa2':
        return 'pink';
      case '00b796':
        return 'teal';
      case 'ffe900':
        return 'yellow';
      case 'ffffff':
        return 'white';
      default:
        throw new \BgaVisibleSystemException(
          'Failed to translate player color from hex value to name: ' . $this->seat_color()
        );
    }
  }

  /**
    @param mixed[] $props
  */
  function update(World $world, $props): void
  {
    $world->table()->updateSeat($this->id(), $props);
  }

  /**
    @return Card[]
  */
  function hand(World $world)
  {
    return $world->table()->mainDeck->getAll(['HAND'], $this->id());
  }
}
