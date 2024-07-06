<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait NextTurn
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\TurnOrderTrait;

  public function stNextTurn()
  {
    // XXX: TODO: This

    $this->notifyAllPlayers('XXX_message', 'ST_NEXT_TURN', []);

    $this->activateNextSeat();

    // XXX: Sometimes we'll need to take transition T_BEGIN_BOT_TURN instead!
    $this->world()->nextState(T_BEGIN_HUMAN_TURN);
  }
}
