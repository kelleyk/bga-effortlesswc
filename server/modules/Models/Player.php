<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\World;

class Player extends \WcLib\PlayerBase
{
  /**
    @param string[] $row
    @return Player
  */
  public static function fromRow($row)
  {
    $that = parent::fromRowBase(Player::class, $row);

    return $that;
  }

  /**
    @return Player[]
  */
  public static function getAll(World $world)
  {
    return array_map(function ($row) {
      return Player::fromRow($row);
    }, $world->table()->rawGetPlayers());
  }

  public function renderForClient()
  {
    return array_merge(parent::renderForClient(), []);
  }
}
