<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\Models\Location;
use EffortlessWC\Models\Seat;
use EffortlessWC\World;

class EffortPile
{
  // N.B.: Reserve effort piles do not currently have IDs.  We could consider changing that.
  protected int $id_;
  protected ?int $location_id_;
  protected int $seat_id_;
  protected int $qty_;

  public static function getById(World $world, int $id): ?EffortPile
  {
    return self::fromRow($world->table()->rawGetEffortPileById($id));
  }

  public static function mustGetById(World $world, int $id): EffortPile
  {
    $pile = self::getById($world, $id);
    if ($pile === null) {
      throw new \WcLib\Exception('Could not find pile with id=' . $id);
    }
    return $pile;
  }

  /**
    @return EffortPile[]
  */
  public static function getAll(World $world)
  {
    return array_map(function ($row) {
      return EffortPile::fromRow($row);
    }, $world->table()->rawGetEffortPiles());
  }

  /**
    @param string[] $row
    @return ?EffortPile
  */
  public static function fromRow($row)
  {
    if ($row === null) {
      throw new \BgaVisibleSystemException('Cannot construct `EffortPile` from null $row.');
    }

    $pile = new EffortPile();

    $pile->id_ = intval($row['id']);
    $pile->location_id_ = $row['location_id'] === null ? null : intval($row['location_id']);
    $pile->seat_id_ = intval($row['seat_id']);
    $pile->qty_ = intval($row['qty']);

    return $pile;
  }

  public function id(): int
  {
    return $this->id_;
  }

  // Returns the number of effort cubes in this pile.
  public function qty(): int
  {
    return $this->qty_;
  }

  public function seat(World $world): Seat
  {
    return Seat::mustGetById($world, $this->seat_id_);
  }

  public function location(World $world): Location
  {
    return Location::mustGetById($world, $this->location_id_);
  }

  public function maybeLocation(World $world): ?Location
  {
    return Location::getById($world, $this->location_id_);
  }

  // N.B.: $n may be null, but the result may not make the effort pile contain fewer than zero.
  public function addEffort(World $world, int $n): void
  {
    if ($n + $this->qty() < 0) {
      throw new \BgaVisibleSystemException('Effort piles cannot shrink below zero items.');
    }

    $this->qty_ += $n;
    $world->table()->updateEffort($this->id(), $this->qty());
  }

  public function renderForClient(World $world): array
  {
    return [
      'id' => $this->id(),
      'seatId' => $this->seat_id_,
      'locationId' => $this->location_id_,
      'qty' => $this->qty(),
    ];
  }
}

// class ReserveEffortPile extends EffortPile
// {
//   function __construct(int $qty, Seat $seat)
//   {
//     parent::__construct($qty, $seat);
//   }

//   public function addEffort(World $world, int $n): void
//   {
//     if ($n + $this->qty() < 0) {
//       throw new \BgaVisibleSystemException('Effort piles cannot shrink below zero items.');
//     }

//     // XXX: This won't update the $reserve_effort stored in $seat, so subsequent calls to `reserveEffort()` will return
//     // an EffortPile with the old number in it.
//     //
//     // XXX: Should we have a mode where we re-read from the database to ensure we always have current $qty?
//     $this->qty_ += $n;
//     $this->seat()->update($world, [
//       'reserve_effort' => $this->qty(),
//     ]);
//   }

//   public function renderForClient(World $world): array
//   {
//     return [
//       'pileType' => 'reserve',
//       'seatId' => $this->seat()->id(),
//       'qty' => $this->qty(),
//     ];
//   }
// }

// class LocationEffortPile extends EffortPile
// {
//   protected Location $location_;

//   function __construct(int $qty, Seat $seat, Location $location)
//   {
//     parent::__construct($qty, $seat);
//     $this->location_ = $location;
//   }

//   public function location(): ?Location
//   {
//     return $this->location_;
//   }

//   public function addEffort(World $world, int $n): void
//   {
//     if ($n + $this->qty() < 0) {
//       throw new \BgaVisibleSystemException('Effort piles cannot shrink below zero items.');
//     }

//     $this->qty_ += $n;
//     $world->table()->updateEffort($this->location()->locationArg(), $this->seat()->id(), $this->qty());
//   }

//   public function renderForClient(World $world): array
//   {
//     return [
//       'pileType' => 'location',
//       'seatId' => $this->seat()->id(),
//       'locationId' => $this->location()->id(),
//       'qty' => $this->qty(),
//     ];
//   }
// }
