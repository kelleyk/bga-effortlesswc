<?php declare(strict_types=1);

namespace Effortless\States;

use Effortless\Util\InputRequiredException;
use Effortless\Models\Location;

trait PlaceEffort
{
  use \Effortless\BaseTableTrait;
  use \Effortless\Util\ParameterInput;

  // N.B.: This is a separate function because we need to be able to call it from `regenerateParamInputConfig()` in
  // tests (after we've changed the set of locations or other game state).
  /** @return Location[]  */
  public function getValidEffortPlacementTargets()
  {
    $world = $this->world();
    $active_seat = $world->activeSeat();

    return array_filter($world->locations(), function ($location) use ($world, $active_seat) {
      return $location->canVisit($world, $active_seat);
    });
  }

  public function stPlaceEffort()
  {
    $this->triggerStateEvents();

    $world = $this->world();

    // XXX: I think that this is a postcondition for `getParameterLocation()`, right?  Either you've consumed a value
    // from the value-stack or you've thrown `InputRequiredException.`
    $this->world()->table()->debug('*** stPlaceEffort(): before call to `getParameter()`');
    $paraminput_config = $this->world()->table()->getParamInputConfig();
    if ($paraminput_config !== null) {
      $this->world()->table()->dump('paraminput_config', $paraminput_config->jsonSerialize());
    }

    try {
      $location = $this->getParameterLocation($world, $this->getValidEffortPlacementTargets(), [
        'description' => '${actplayer} must pick a location to visit.',
        'descriptionmyturn' => '${you} must pick a location to visit.',
      ]);
    } catch (InputRequiredException $e) {
      return;
    }

    $this->world()->table()->debug('*** stPlaceEffort(): after call to `getParameter()`');
    $paraminput_config = $this->world()->table()->getParamInputConfig();
    if ($paraminput_config !== null) {
      $this->world()->table()->dump('paraminput_config', $paraminput_config->jsonSerialize());
    }

    // Move one effort from the active seat's reserve to their effort-pile at that location.
    $world->moveEffort(
      $world->activeSeat()->reserveEffort($world),
      $location->effortPileForSeat($world, $world->activeSeat())
    );

    $this->notifyAllPlayers('message', '${seat} visits ${location} and places an effort there.', [
      'seat' => $world->activeSeat()->renderForNotif($world),
      'location' => $location->renderForNotif($world),
    ]);
    $this->setGameStateInt(GAMESTATE_INT_VISITED_LOCATION, $location->id());

    $world->nextState(T_DONE);
  }

  public function argPlaceEffort()
  {
    return $this->renderBoardState();
  }
}
