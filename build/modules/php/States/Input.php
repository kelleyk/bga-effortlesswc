<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait Input
{
  use \EffortlessWC\BaseTableTrait;

  public function stInput()
  {
  }

  public function argInput()
  {
    return $this->renderBoardState();
  }
}
