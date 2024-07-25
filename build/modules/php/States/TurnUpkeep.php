<?php declare(strict_types=1);

namespace Effortless\States;

trait TurnUpkeep
{
  use \Effortless\BaseTableTrait;

  public function stTurnUpkeep()
  {
    // XXX: TODO: We'll need to go to ST_ROUND_UPKEEP if this is the end of the round.

    $this->notifyAllPlayers('XXX_message', 'ST_TURN_UPKEEP', []);

    // Turn step C: refill locations, top to bottom.
    $this->fillSetlocCards();

    $this->world()->nextState(T_DONE);
  }
}
