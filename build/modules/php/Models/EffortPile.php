<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\Models\Location;
use EffortlessWC\Models\Seat;
use EffortlessWC\World;

abstract class EffortPile
{
  protected int $qty_;
  protected Seat $seat_;

  function __construct(int $qty, Seat $seat)
  {
    $this->qty_ = $qty;
    $this->seat_ = $seat;
  }

  // Returns the number of effort cubes in this pile.
  public function qty(): int
  {
    return $this->qty_;
  }

  public function seat(): Seat
  {
    return $this->seat_;
  }

  // N.B.: $n may be null, but the result may not make the effort pile contain fewer than zero.
  abstract public function addEffort(World $world, int $n): void;
}

class ReserveEffortPile extends EffortPile
{
  function __construct(int $qty, Seat $seat)
  {
    parent::__construct($qty, $seat);
  }

  public function addEffort(World $world, int $n): void
  {
    if ($n + $this->qty() < 0) {
      throw new \BgaVisibleSystemException('Effort piles cannot shrink below zero items.');
    }

    // XXX: This won't update the $reserve_effort stored in $seat, so subsequent calls to `reserveEffort()` will return
    // an EffortPile with the old number in it.
    //
    // XXX: Should we have a mode where we re-read from the database to ensure we always have current $qty?
    $this->qty_ += $n;
    $this->seat()->update($world, [
      'reserve_effort' => $this->qty(),
    ]);
  }
}

class LocationEffortPile extends EffortPile
{
  protected Location $location_;

  function __construct(int $qty, Seat $seat, Location $location)
  {
    parent::__construct($qty, $seat);
    $this->location_ = $location;
  }

  public function location(): ?Location
  {
    return $this->location_;
  }

  public function addEffort(World $world, int $n): void
  {
    if ($n + $this->qty() < 0) {
      throw new \BgaVisibleSystemException('Effort piles cannot shrink below zero items.');
    }

    $this->qty_ += $n;
    $world->table()->updateEffort($this->location()->locationArg(), $this->seat()->id(), $this->qty());
  }
}
