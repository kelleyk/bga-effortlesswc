<?php declare(strict_types=1);

namespace Effortless\States;

use Effortless\Util\InputRequiredException;

trait BotTurn
{
  use \Effortless\BaseTableTrait;

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
