<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait NextTurn
{
  use \EffortlessWC\BaseTableTrait;

  public function stNextTurn()
  {
    $this->activeNextPlayer();

    // XXX: ...
    $this->world()->nextState(T_BEGIN_HUMAN_TURN);
  }
}
