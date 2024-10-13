<?php declare(strict_types=1);

namespace Effortless\States;

trait PostScoring
{
  use \Effortless\BaseTableTrait;

  public function stPostScoring()
  {
    $this->triggerStateEvents();

    $world = $this->world();

    $world->ruleset()->setBgaScore($world, \Effortless\calculateScores($world));

    $world->nextState(T_DONE);
  }

  public function argPostScoring()
  {
  }
}
