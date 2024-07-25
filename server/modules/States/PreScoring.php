<?php declare(strict_types=1);

namespace Effortless\States;

trait PreScoring
{
  use \Effortless\BaseTableTrait;

  public function stPreScoring()
  {
    $world = $this->world();

    $world->nextState(T_DONE);
  }
}
