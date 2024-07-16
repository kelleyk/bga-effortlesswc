<?php declare(strict_types=1);

namespace EffortlessWC;

trait DatabaseTrait
{
  use \EffortlessWC\BaseTableTrait;

  // -----------
  // Database interface
  // -----------

  // N.B.: Location and Setting are implemented as WcDeck cards, so we use the `get()` function on the appropriate deck (`table()->*deck->get()` et al.) to fetch those.

  function rawGetSeatById(int $seat_id)
  {
    return self::getObjectFromDB('SELECT * FROM `seat` WHERE `id` = ' . $seat_id);
  }

  function rawGetSeats()
  {
    return self::getCollectionFromDB('SELECT * FROM `seat` WHERE TRUE');
  }

  function rawGetPlayerById(string $player_id)
  {
    return self::getObjectFromDB('SELECT * FROM `player` WHERE `player_id` = "' . $player_id . '"');
  }

  function rawGetPlayers()
  {
    return self::getCollectionFromDB('SELECT * FROM `player` WHERE TRUE');
  }

  function rawGetEffortPileById(int $pile_id)
  {
    return self::getObjectFromDB('SELECT * FROM `effort_pile` WHERE `id` = ' . $pile_id);
  }

  function rawGetReserveEffortPile(int $seat_id)
  {
    $sql = 'SELECT * FROM `effort_pile` WHERE `seat_id` = ' . $seat_id . ' AND `location_id` IS NULL';
    $row = self::getObjectFromDB($sql);
    if ($row === null) {
      throw new \feException('No results for query: ' . $sql);
    }
    return $row;
  }

  function rawGetEffortPilesBySeat(int $location_index)
  {
    $rows = self::getCollectionFromDB('SELECT * FROM `effort_pile` WHERE `location_id` = ' . $location_index);

    $effort = [];
    foreach ($rows as $row) {
      $effort[intval($row['seat_id'])] = $row;
    }
    return $effort;
  }

  function updateEffort(int $id, int $qty)
  {
    return self::DbQuery('UPDATE `effort_pile` SET `qty` = ' . $qty . ' WHERE `id` = ' . $id);
  }

  /**
    @param mixed[] $props
   */
  function updateSeat(int $seat_id, $props): void
  {
    $values = $this->buildUpdateValues($props);
    self::DbQuery('UPDATE `seat` SET ' . implode(',', $values) . ' WHERE `id` = ' . $seat_id);
  }

  // XXX: This is cribbed from Burgle Bros 2; we should move it somewhere more reusable.
  /**
    @param mixed[] $props
    @return string[]
  */
  private function buildUpdateValues($props)
  {
    $values = [];
    foreach ($props as $k => $v) {
      if (is_null($v)) {
        $values[] = $k . ' = NULL';
        // } elseif ($v instanceof Position) {
        //   $values[] = $this->buildExprUpdatePos($v);
      } elseif (is_bool($v)) {
        $values[] = $k . ' = ' . ($v ? 'TRUE' : 'FALSE');
      } elseif (is_int($v)) {
        $values[] = $k . ' = ' . $v;
      } else {
        $values[] = $k . ' = "' . self::escapeStringForDB($v) . '"';
      }
    }
    return $values;
  }
}
