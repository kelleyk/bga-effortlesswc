<?php declare(strict_types=1);

namespace Effortless;

// Used during end-game scoring.
class ScoringContext
{
  private $scores_;

  // $points may be positive or negative.
  public function givePoints(int $seatId, int $points)
  {
    // echo '*** givePoints(seatId='.$seatId.', points='.$points.'): before giving points: ' . print_r($this->scores_, true) . "\n-------(end before)\n";
    $this->scores_[$seatId] = ($this->scores_[$seatId] ?? 0) + $points;
    // echo '*** givePoints(): after giving points: ' . print_r($this->scores_, true) . "\n-------(end after)\n";
  }

  public function scores()
  {
    return $this->scores_;
  }
}
