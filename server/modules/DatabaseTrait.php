<?php declare(strict_types=1);

namespace Effortless;

/*
This query matches up settings and locations that match on sublocation and sublocation_index (i.e. that are in play and
in the same place on the table) in a way that MySQL 5.7 likes.  (Specifically, it selects all of the in-play settings,
and adds a `location_id` column that holds the ID of the corresponding location.)  The WITH-AS syntax from ANSI SQL99
(CTEs) is not supported prior to MySQL 8.0.

  SELECT setting.*, location.id AS location_id FROM
    (SELECT * FROM card WHERE card_type_group = 'setting' AND card.card_sublocation = 'SETLOC') AS setting
  LEFT JOIN
    (SELECT * FROM card WHERE card_type_group = 'location' AND card.card_sublocation = 'SETLOC') AS location
    ON setting.card_sublocation = location.card_sublocation
      AND setting.card_sublocation_index = location.card_sublocation_index

TODO: It'd be neat to teach WcDeck to use a query like this one so that we could simply have "location_id" be a property
on our Setting "cards".
 */

trait DatabaseTrait
{
  use \Effortless\BaseTableTrait;

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

  function rawGetEffortPiles()
  {
    return self::getCollectionFromDB('SELECT * FROM `effort_pile`');
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

  function rawGetEffortPilesBySeat(int $location_id)
  {
    $rows = self::getCollectionFromDB('SELECT * FROM `effort_pile` WHERE `location_id` = ' . $location_id);

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

  function rawGetSetLocPairs()
  {
    // N.B.: MySQL apparently doesn't support full/outer joins, so we use an inner join and assert that we found all six
    // pairs.  If this isn't true, then one of our table-state invariants has been violated.
    $rows = self::getCollectionFromDB(
      <<<END_QUERY
SELECT setting.id AS setting_id, location.id AS location_id
FROM
  (SELECT id, card_sublocation, card_sublocation_index
   FROM card
   WHERE card_type_group = 'setting' AND card.card_sublocation = 'SETLOC'
  ) AS setting
INNER JOIN
  (SELECT id, card_sublocation, card_sublocation_index
   FROM card
   WHERE card_type_group = 'location' AND card.card_sublocation = 'SETLOC'
  ) AS location
ON
  setting.card_sublocation = location.card_sublocation
  AND setting.card_sublocation_index = location.card_sublocation_index
END_QUERY
    );

    // XXX: This won't be true if/when we add support for any game effects that cause settings or locations to be
    // discarded without replacement.
    if (count($rows) != 6) {
      throw new \BgaVisibleSystemException(
        'Unable to find all setting-location mappings; expected 6 rows, but got ' . count($rows) . '.'
      );
    }

    return $rows;
  }

  // Returns a map from setting ID to location ID indicating which pairs share a location on the table.
  /** @return int[] */
  function getSetToLocMap()
  {
    $set_to_loc = [];
    foreach ($this->rawGetSetLocPairs() as $row) {
      $set_to_loc[intval($row['setting_id'])] = intval($row['location_id']);
    }
    return $set_to_loc;
  }

  // Returns a map from location ID to setting ID indicating which pairs share a location on the table.
  /** @return int[] */
  function getLocToSetMap()
  {
    $loc_to_set = [];
    foreach ($this->rawGetSetLocPairs() as $row) {
      $loc_to_set[intval($row['location_id'])] = intval($row['setting_id']);
    }
    return $loc_to_set;
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
