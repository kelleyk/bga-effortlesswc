<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait NextTurn
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\TurnOrderTrait;

  public function stNextTurn()
  {
    // $this->activeNextPlayer();
    $this->activateNExtSeat();

    // XXX: ...
    $this->world()->nextState(T_BEGIN_HUMAN_TURN);
  }
}
