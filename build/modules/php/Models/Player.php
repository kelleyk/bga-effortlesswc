<?php declare(strict_types=1);

namespace Effortless\Models;

use Effortless\World;

class Player extends \WcLib\PlayerBase
{
  public static function getById(World $world, string $id): ?Player
  {
    return self::fromRow($world->table()->rawGetPlayerById($id));
  }

  public static function mustGetById(World $world, string $id): Player
  {
    $player = self::getById($world, $id);
    if ($player === null) {
      throw new \WcLib\Exception('Could not find player with id=' . $id);
    }
    return $player;
  }

  /**
    @param string[] $row
    @return ?Player
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

  public function renderForNotif(World $world): string
  {
    return 'Player[' . $this->id() . ']';
  }
}
