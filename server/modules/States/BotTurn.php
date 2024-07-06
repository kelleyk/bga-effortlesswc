<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\InputRequiredException;

trait BotTurn
{
  use \EffortlessWC\BaseTableTrait;

  public function stBotTurn()
  {
    $world = $this->world();

    try {
      $world->ruleset()->onBotTurn($world);
    } catch (InputRequiredException $e) {
      return;
    }

    $world->nextState(T_DONE);
  }
}
