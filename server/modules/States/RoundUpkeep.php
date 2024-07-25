<?php declare(strict_types=1);

namespace Effortless\States;

trait RoundUpkeep
{
  use \Effortless\BaseTableTrait;

  public function stRoundUpkeep()
  {
    $this->world()->nextState(T_DONE);
  }
}
