<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\InputRequiredException;

trait ResolveLocation
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\Util\ParameterInput;

  public function stResolveLocation()
  {
    $world = $this->world();

    $visited_location = $world->visitedLocation();
    $active_seat = $world->activeSeat();

    // XXX: Do we need the $active_seat param if it's available from $world?
    try {
      $visited_location->onVisited($world, $active_seat);
    } catch (InputRequiredException $e) {
      return;
    }

    $this->world()->nextState(T_DONE);
  }
}
