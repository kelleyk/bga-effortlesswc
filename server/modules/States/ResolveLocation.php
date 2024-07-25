<?php declare(strict_types=1);

namespace Effortless\States;

use Effortless\Util\InputRequiredException;

trait ResolveLocation
{
  use \Effortless\BaseTableTrait;
  use \Effortless\Util\ParameterInput;

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
