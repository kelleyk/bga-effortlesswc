<?php declare(strict_types=1);

namespace EffortlessWC;

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
}
