<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\InputRequiredException;

trait PlaceEffort
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\Util\ParameterInput;

  public function stPlaceEffort()
  {
    $world = $this->world();

    try {
      $location = $this->getParameterLocation($world, $world->locations(), [
        'description' => '${actplayer} must decide where to place one of their Effort.',
        'descriptionmyturn' => '${you} must decide where to place one of your Effort.',
      ]);
    } catch (InputRequiredException $e) {
      return;
    }

    // Move one effort from the active seat's reserve reserve to their effort-pile at that location.
    $world->moveEffort(
      $world->activeSeat()->reserveEffort(),
      $location->effortPileForSeat($world, $world->activeSeat())
    );

    $this->notifyAllPlayers('XXX_message', 'An effort is placed!', [
      // XXX: give seat, location details
    ]);
    $this->setGameStateInt(GAMESTATE_INT_VISITED_LOCATION, $location->id());

    $world->nextState(T_DONE);
  }

  public function argPlaceEffort()
  {
    return $this->renderBoardState();
  }
}
