<?php declare(strict_types=1);

namespace EffortlessWC\Util;

use EffortlessWC\World;
use EffortlessWC\Models\Seat;
use EffortlessWC\Models\Location;

class UserInputRequiredException extends \Exception
{
}

trait Parameters
{
  // XXX: We're going to try this without $param_index this time.

  // XXX: The `getParameter()` stuff should be moved to their own trait; they throw a special exception if we need to
  // ask for user input still.
  public function getParameterEffortPile(World $world, $valid_targets)
  {
    throw new \feException('XXX: foo');
  }

  public function getParameterLocation(World $world, $valid_targets)
  {
    throw new \feException('XXX: foo');
  }

  public function getParameterCardInHand(World $world, Seat $seat)
  {
    // XXX: This should be a thin layer over `getParameterCard()`.
    throw new \feException('XXX: foo');
  }

  // XXX: This needs to show the player making the decision any face-down cards when they make their decision.
  public function getParameterCardAtLocation(World $world, Location $loc)
  {
    // XXX: This should be a thin layer over `getParameterCard()`.
    throw new \feException('XXX: foo');
  }

  // Each element of $valid_targets is a `Card`.
  public function getParameterCard(World $world, $valid_targets)
  {
    throw new \feException('XXX: foo');
  }
}
