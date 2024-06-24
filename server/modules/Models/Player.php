<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\World;

class Player extends \WcLib\PlayerBase
{
  protected int $reserve_effort_;

  /**
    @param string[] $row
    @return Player
  */
  public static function fromRow($row)
  {
    $that = parent::fromRowBase(Player::class, $row);

    $that->reserve_effort_ = intval($row['reserve_effort']);

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
    return array_merge(parent::renderForClient(), [
      'reserveEffort' => $this->reserve_effort_,
    ]);
  }
}
