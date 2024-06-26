<?php declare(strict_types=1);

namespace EffortlessWC;

require_once 'WcLib/BgaTableTrait.php';
require_once 'WcLib/GameState.php';
require_once 'WcLib/WcDeck.php';

use EffortlessWC\World;
use EffortlessWC\WorldImpl;
use EffortlessWC\Models\Seat;

trait BaseTableTrait
{
  use \WcLib\BgaTableTrait;
  use \WcLib\GameState;
<<<<<<< HEAD
||||||| parent of 65f9cf9 (Get game running again on BGA Studio)
  use \EffortlessWC\RenderTrait;
=======
  use \WcLib\Logging;
  use \EffortlessWC\RenderTrait;
>>>>>>> 65f9cf9 (Get game running again on BGA Studio)

  public \WcLib\WcDeck $mainDeck;
  public \WcLib\WcDeck $locationDeck;
  public \WcLib\WcDeck $settingDeck;

  public function world(): World
  {
    return new WorldImpl($this);
  }

  // XXX: Move to better home
  public function renderForClient(World $world, $x)
  {
    if (is_array($x)) {
      return array_map(function ($y) use ($world) {
        return $this->renderForClient($world, $y);
      }, $x);
    }
    return $x->renderForClient($world);
  }

  // This function returns everything we need to refresh all mutable state.
  public function renderBoardState()
  {
    // XXX: Things still to be done here:
    //
    // - Need to send public and/or private data about each seat (e.g. their hand), depending on the ruleset.
    //

    $world = $this->world();
    return [
      'seats' => $this->renderForClient($world, Seat::getAll($world)),
      'cards' => $this->renderForClient($world, $this->mainDeck->getAll(['SETLOC', 'DISCARD'])),
      'locations' => $this->renderForClient($world, $this->locationDeck->getAll(['SETLOC'])),
      'settings' => $this->renderForClient($world, $this->settingDeck->getAll(['SETLOC'])),
    ];
  }
}
