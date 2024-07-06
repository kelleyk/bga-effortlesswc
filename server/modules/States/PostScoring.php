<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait PostScoring
{
  use \EffortlessWC\BaseTableTrait;

  public function stPostScoring()
  {
    $world = $this->world();

    $world->nextState(T_DONE);
  }

  public function argPostScoring()
  {
    // XXX: Return all scoring info so that the client can draw the end-game screen.
  }
}
