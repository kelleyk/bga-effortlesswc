<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait RoundUpkeep
{
  use \EffortlessWC\BaseTableTrait;

  public function stRoundUpkeep()
  {
    $this->world()->nextState(T_DONE);
  }
}
