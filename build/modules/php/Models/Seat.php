<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\World;
use EffortlessWC\Models\EffortPile;

class Seat extends \WcLib\SeatBase
{
  protected int $reserve_effort_;

  public static function getById(World $world, int $id): ?Seat
  {
    return self::fromRow($world->table()->rawGetSeatById($id));
  }

  public static function mustGetById(World $world, int $id): Seat
  {
    $seat = self::getById($world, $id);
    if ($seat === null) {
      throw new \BgaVisibleSystemException('Could not find seat with id=' . $id);
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
      $that->reserve_effort_ = intval($row['reserve_effort']);
    }

    return $that;
  }

  public function reserveEffort(): EffortPile
  {
    throw new \feException('XXX:');
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
      'reserveEffort' => $this->reserve_effort_,
      'colorName' => $this->color_name(),
    ]);
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
        throw new \BgaVisibleSystemException('Failed to translate player color from hex value to name.');
    }
  }

  /**
    @param mixed[] $props
  */
  function update(World $world, $props): void
  {
    $world->table()->updateSeat($this->id(), $props);
  }
}
