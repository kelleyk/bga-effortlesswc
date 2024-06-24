<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\World;

class Seat extends \WcLib\SeatBase
{
  protected int $reserve_effort_;

  /**
    @param string[] $row
    @return Seat
  */
  public static function fromRow($row)
  {
    $that = parent::fromRowBase(Seat::class, $row);

    $that->reserve_effort_ = intval($row['reserve_effort']);

    return $that;
  }

  public function reserve_effort(): int
  {
    return $this->reserve_effort_;
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
    ]);
  }
}
