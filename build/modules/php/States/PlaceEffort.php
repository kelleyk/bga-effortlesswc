<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\UserInputRequiredException;

trait PlaceEffort
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\Util\Parameters;

  public function stPlaceEffort()
  {
    try {
      $location = $this->getParameterLocation($this->world(), $this->world()->locations());

      // Move one effort from the active seat's reserve reserve to their effort-pile at that location.
      $this->world()->moveEffort(
        $this->world()->activeSeat()->reserveEffort(),
        $location->effortPileForSeat($this->world(), $this->world()->activeSeat())
      );

      $this->world()->nextState(T_DONE);
    } catch (UserInputRequiredException $e) {
      $this->world()->nextState(T_GET_INPUT);
    }
  }

  public function argPlaceEffort()
  {
    return $this->renderBoardState();
  }
}
