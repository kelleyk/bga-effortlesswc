<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\InputRequiredException;

trait PlaceEffort
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\Util\ParameterInput;

  public function stPlaceEffort()
  {
    try {
      $location = $this->getParameterLocation($this->world(), $this->world()->locations(), [
        'description' => '${actplayer} must decide where to place one of their Effort.',
        'descriptionmyturn' => '${you} must decide where to place one of your Effort.',
      ]);

      // Move one effort from the active seat's reserve reserve to their effort-pile at that location.
      $this->world()->moveEffort(
        $this->world()->activeSeat()->reserveEffort(),
        $location->effortPileForSeat($this->world(), $this->world()->activeSeat())
      );

      $this->world()->nextState(T_DONE);
    } catch (InputRequiredException $e) {
      return;
    }
  }

  public function argPlaceEffort()
  {
    return $this->renderBoardState();
  }
}
