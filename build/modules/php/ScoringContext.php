<?php declare(strict_types=1);

namespace EffortlessWC;

// Used during end-game scoring.
class ScoringContext
{
  // $points may be positive or negative.
  public function givePoints(int $seatId, int $points)
  {
    throw new \feException('XXX: no impl');
  }
}
