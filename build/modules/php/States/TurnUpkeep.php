<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait TurnUpkeep
{
  use \EffortlessWC\BaseTableTrait;

  public function stTurnUpkeep()
  {
    // XXX: TODO: We'll need to go to ST_ROUND_UPKEEP if this is the end of the round.

    $this->notifyAllPlayers('XXX_message', 'ST_TURN_UPKEEP', []);

    $this->world()->nextState(T_DONE);
  }
}
