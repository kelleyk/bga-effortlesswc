<?php declare(strict_types=1);

namespace Effortless;

use Effortless\Models\Seat;

abstract class Ruleset
{
  public function onSetup(World $world)
  {
    // XXX: Should this be abstract?
  }

  public function onBotTurn(World $world)
  {
    throw new \BgaVisibleSystemException('This ruleset does not support bot players.');
  }

  public function setBgaScore(World $world, TableScore $table_score): void
  {
    foreach (Seat::getAll($world) as $seat) {
      if ($seat->player_id() !== null) {
        $total_score = $table_score->by_seat[$seat->id()]->total();

        // XXX: This is what the wiki says to do, but there *must* be APIs for manipulating score... right?
        $world
          ->table()
          ->DbQuery('UPDATE player SET player_score=' . $total_score . ' WHERE player_id="' . $seat->player_id() . '"');
      }
    }
  }

  public function isScoringSeat(World $world, Seat $seat): bool
  {
    return $seat->player_id() !== null;
  }
}
