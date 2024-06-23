<?php declare(strict_types=1);

namespace EffortlessWC;

require_once 'WcLib/BgaTableTrait.php';
require_once 'WcLib/GameState.php';
require_once 'WcLib/WcDeck.php';

use EffortlessWC\World;
use EffortlessWC\WorldImpl;

trait BaseTableTrait
{
  use \WcLib\BgaTableTrait;
  use \WcLib\GameState;

  public \WcLib\WcDeck $mainDeck;
  public \WcLib\WcDeck $locationDeck;
  public \WcLib\WcDeck $settingDeck;

  public function world(): World
  {
    return new WorldImpl($this);
  }
}
