<?php declare(strict_types=1);

namespace EffortlessWC;

use EffortlessWC\Models\Seat;

trait TurnOrderTrait
{
  use \EffortlessWC\BaseTableTrait;

  // Randomly assign a unique turn order to each character.
  //
  // Called in the ST_FINISH_SETUP state, after the list of
  // player-characters is finalized.
  function finishSetupTurnOrder()
  {
    $seats = Seat::getAll($this->world());

    // TODO: Probably need to support some other
    // (game-option-driven) ways of choosing player order, but
    // random will be fine for now.
    shuffle($seats);

    $i = 0;
    foreach ($seats as $seat) {
      $seat->update($this->world(), [
        'turn_order' => $i++,
      ]);
    }

    // Initialize gamestate related to turn order.
    $this->setGameStateInt(GAMESTATE_INT_ACTIVE_SEAT, -1);
  }

  public function activateNextSeat(): void
  {
    $this->setGameStateInt(
      GAMESTATE_INT_ACTIVE_SEAT,
      $this->getNextSeatInTurnOrder($this->getGameStateInt(GAMESTATE_INT_ACTIVE_SEAT))
    );
  }

  // Returns the ID of the seat that goes next in the turn order after `$turnOrder`.
  //
  // N.B.: This assumes that `turn_order` values have been uniquely assigned.
  function getNextSeatInTurnOrder(int $turnOrder): int
  {
    $row = self::getObjectFromDB(
      'SELECT * FROM `seat` WHERE `turn_order` > ' . $turnOrder . ' ORDER BY `turn_order` ASC LIMIT 1'
    );
    if (is_null($row)) {
      // There's nobody after that point in the turn order;
      // start over with the character that has the lowest
      // turn-order value.
      $row = self::getObjectFromDB('SELECT * FROM `seat` ORDER BY `turn_order` ASC LIMIT 1');
    }
    return intval($row['id']);
  }
}
