<?php declare(strict_types=1);

namespace Effortless;

require_once 'WcLib/BgaTableTrait.php';
require_once 'WcLib/GameState.php';
require_once 'WcLib/WcDeck.php';

use Effortless\World;
use Effortless\WorldImpl;
use Effortless\Models\Seat;
use Effortless\Models\EffortPile;

// XXX: This doesn't belong here.
function setDefault(array &$array, $key, mixed $default)
{
  if (!array_key_exists($key, $array)) {
    $array[$key] = $default;
  }

  return $array[$key];
}

trait BaseTableTrait
{
  use \WcLib\BgaTableTrait;
  use \WcLib\GameState;
  use \WcLib\Logging;
  use \Effortless\RenderTrait;

  public \WcLib\WcDeck $mainDeck;
  public \WcLib\WcDeck $locationDeck;
  public \WcLib\WcDeck $settingDeck;
  public \WcLib\ValueStack $valueStack;

  public function world(): World
  {
    return new WorldImpl($this);
  }

  abstract function triggerStateEvents(): void;

  // XXX: Move to better home
  public function renderForClient(World $world, $x, ...$args)
  {
    if (is_array($x)) {
      return array_map(function ($y) use ($world, $args) {
        return $this->renderForClient($world, $y, ...$args);
      }, $x);
    }
    return $x->renderForClient($world, ...$args);
  }

  // This function returns everything we need to refresh all mutable state.
  //
  // N.B.: Remember that the "_private" key is supported only in state args, and not in gamedatas.
  public function renderBoardState(bool $include_private = true)
  {
    // XXX: Things still to be done here:
    //
    // - Need to send public and/or private data about each seat (e.g. their hand), depending on the ruleset.
    //

    $world = $this->world();

    $board_state = [
      'mutableBoardState' => [
        'seats' => $this->renderForClient($world, Seat::getAll($world)),
        'cards' => $this->renderForClient($world, $this->mainDeck->getAll(['SETLOC', 'DISCARD'])),
        'locations' => $this->renderForClient($world, $this->locationDeck->getAll(['SETLOC'])),
        'settings' => $this->renderForClient($world, $this->settingDeck->getAll(['SETLOC'])),
        'effortPiles' => $this->renderForClient($world, EffortPile::getAll($world), calculateAllSettingScores($world)),
      ],
    ];

    if ($include_private) {
      $board_state = array_merge($board_state, [
        '_private' => $this->renderPrivateState(),
      ]);
    }

    return $board_state;
  }

  public function renderPrivateState()
  {
    $world = $this->world();

    $private_args = [];

    foreach (Seat::getAll($world) as $seat) {
      $player_id = $seat->player_id();
      if ($player_id !== null) {
        setDefault($private_args, $player_id, []);

        $private_args[$player_id]['cards'] = array_merge(
          $private_args[$player_id]['cards'] ?? [],
          $this->renderForClient($world, $this->mainDeck->getAll(['HAND'], $seat->id()))
        );
      }
    }

    return $private_args;
  }

  public function fillSetlocCards()
  {
    foreach ($this->world()->locations() as $loc) {
      $this->world()->fillCards($loc);
    }
  }
}
