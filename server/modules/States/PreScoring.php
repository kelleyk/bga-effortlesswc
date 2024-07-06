<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait PreScoring
{
  use \EffortlessWC\BaseTableTrait;

  public function stPreScoring()
  {
    $world = $this->world();

    $world->nextState(T_DONE);
  }
}
