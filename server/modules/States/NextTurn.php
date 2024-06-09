<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait NextTurn
{
  use \WcLib\BgaTableTrait;

  public function stNextTurn()
  {
    // XXX: ...
    $this->world()->nextState(T_BEGIN_HUMAN_TURN);
  }
}
