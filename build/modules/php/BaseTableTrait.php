<?php declare(strict_types=1);

namespace EffortlessWC;

require_once 'WcLib/BgaTableTrait.php';
require_once 'WcLib/GameState.php';

use EffortlessWC\World;
use EffortlessWC\WorldImpl;

trait BaseTableTrait
{
  use \WcLib\BgaTableTrait;
  use \WcLib\GameState;

  public function world(): World
  {
    return new WorldImpl($this);
  }
}
