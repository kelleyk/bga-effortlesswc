<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait BotTurn
{
  use \EffortlessWC\BaseTableTrait;

  public function stBotTurn()
  {
    $world = $this->world();

    $this->notifyAllPlayers('XXX_message', 'XXX: Skipping bot turn', []);

    // XXX: Double-check the rulebook; but the bot basically randomly picks a location, right?

    $world->nextState(T_DONE);
  }
}
