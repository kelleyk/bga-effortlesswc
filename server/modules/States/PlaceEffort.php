<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait PlaceEffort
{
  use \EffortlessWC\BaseTableTrait;

  public function stPlaceEffort()
  {
  }

  public function argPlaceEffort()
  {
    return $this->renderBoardState();
  }
}
