<?php declare(strict_types=1);

namespace Effortless;

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
    throw new \BgaVisibleSystemException('XXX: no impl for `setBgaScore()` for this ruleset.');
  }
}
