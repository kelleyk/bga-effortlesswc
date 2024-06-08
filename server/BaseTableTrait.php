<?php declare(strict_types=1);

namespace EffortlessWC;

// // XXX:
// require_once 'config.inc.php';
// require_once 'WcLib/BgaTableTrait.php';

use \EffortlessWC\World;
use \EffortlessWC\WorldImpl;

trait BaseTableTrait
{
  use \WcLib\BgaTableTrait;
  use \WcLib\GameState;

  public function world(): World {
    return new WorldImpl($this);
  }
}
