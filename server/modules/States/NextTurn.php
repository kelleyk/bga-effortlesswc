<?php declare(strict_types=1);

namespace Effortless\States;

use Effortless\Models\Player;

trait NextTurn
{
  use \Effortless\BaseTableTrait;
  use \Effortless\TurnOrderTrait;

  public function stNextTurn()
  {
    $this->triggerStateEvents();

    // throw new \feException('XXX: stNextTurn - 000');
    $world = $this->world();

    $this->activateNextSeat();
    $this->activateNextDecidingPlayer();

    // XXX: TODO: This is temporary
    $deciding_player = Player::mustGetById(
      $world,
      '' . $world->table()->getGameStateInt(GAMESTATE_INT_DECIDING_PLAYER)
    );
    // $this->notifyAllPlayers('XXX_message', 'ST_NEXT_TURN: activeSeat=${activeSeat} decidingPlayer=${decidingPlayer}', [
    //   'activeSeat' => $world->activeSeat()->renderForNotif($world),
    //   'decidingPlayer' => $deciding_player->renderForNotif($world),
    // ]);

    if ($world->activeSeat()->reserveEffort($world)->qty() <= 0) {
      // XXX: TODO: Assert that *nobody* has any effort left.  (Or, alternatively, we could skip seats that are out of
      // effort until everybody is; but can the seats ever have different amounts?)
      $world->nextState(T_END_GAME);
    } elseif ($world->activeSeat()->player_id() === null) {
      $world->nextState(T_BEGIN_BOT_TURN);
    } else {
      $world->nextState(T_BEGIN_HUMAN_TURN);
    }

    // Invariant assertions for the parameter-input system.
    $value_stack = $this->valueStack->getValueStack();
    if (count($value_stack) !== 0) {
      throw new \BgaVisibleSystemException(
        'Internal error: value-stack is not empty in ST_NEXT_TURN.  Contents: ' . print_r($value_stack, true)
      );
    }
    $read_index = $world->table()->getNextReadIndex($world);
    $write_index = $world->table()->getNextParameterIndex($world);
    // XXX: re-enable me!
    //
    // if ($read_index != $write_index) {
    //   throw new \BgaVisibleSystemException(
    //     'Read/write parameter index mismatch at turn transition.  read_index=' .
    //       $read_index .
    //       ' and write_index=' .
    //       $write_index
    //   );
    // }
  }

  public function activateNextDecidingPlayer(): void
  {
    $this->setGameStateInt(
      GAMESTATE_INT_DECIDING_PLAYER,
      $this->getNextDecidingPlayerInTurnOrder($this->getGameStateInt(GAMESTATE_INT_DECIDING_PLAYER))
    );
  }

  // Returns the ID of the player that is next in the rotation of human players who will take turns being the "deciding
  // player" when a bot seat has a choice to make.
  //
  // XXX: This uses ints only because the gamestate library doesn't support strings (yet).  BGA player-IDs are better
  // treated as opaque strings.
  function getNextDecidingPlayerInTurnOrder(int $lastPlayerId): int
  {
    $row = self::getObjectFromDB(
      'SELECT * FROM `player` WHERE `player_id` > ' . $lastPlayerId . ' ORDER BY `player_id` ASC LIMIT 1'
    );
    if (is_null($row)) {
      // There's nobody after that point in the turn order;
      // start over with the character that has the lowest
      // turn-order value.
      $row = self::getObjectFromDB('SELECT * FROM `player` ORDER BY `player_id` ASC LIMIT 1');
    }
    return intval($row['player_id']);
  }
}
