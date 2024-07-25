<?php declare(strict_types=1);

namespace Effortless;

// Used during end-game scoring.
class ScoringContext
{
  private $scores_;

  // $points may be positive or negative.
  public function givePoints(int $seatId, int $points)
  {
    $this->scores_[$seatId] = ($this->scores_[$seatId] ?? 0) + $points;
  }

  public function scores()
  {
    return $this->scores_;
  }
}
