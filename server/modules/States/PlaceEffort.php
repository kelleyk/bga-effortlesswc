<?php declare(strict_types=1);

namespace Effortless\States;

use Effortless\Util\InputRequiredException;

trait PlaceEffort
{
  use \Effortless\BaseTableTrait;
  use \Effortless\Util\ParameterInput;

  public function stPlaceEffort()
  {
    $world = $this->world();

    try {
      $location = $this->getParameterLocation($world, $world->locations(), [
        'description' => '${actplayer} must pick a location to visit.',
        'descriptionmyturn' => '${you} must pick a location to visit.',
      ]);
    } catch (InputRequiredException $e) {
      return;
    }

    // Move one effort from the active seat's reserve to their effort-pile at that location.
    $world->moveEffort(
      $world->activeSeat()->reserveEffort($world),
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
