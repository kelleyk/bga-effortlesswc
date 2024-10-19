<?php declare(strict_types=1);

namespace Effortless;

trait GetDiscardPileTrait
{
  use \Effortless\BaseTableTrait;

  // This can be called in any state, so we deliberately don't call `checkAction()`.
  function onActGetDiscardPile(): void
  {
    $world = $this->world();
    $player_id = $this->getCurrentPlayerId();

    $this->notifyPlayer($player_id, 'discardPile', 'XXX: notif for discardPile', [
      'cards' => $this->renderForClient($world, $world->table()->mainDeck->getAll(['DISCARD'])),
    ]);
  }
}
