<?php declare(strict_types=1);

namespace WcLib;

// // XXX: We really shouldn't need to define this here.
// const APP_GAMEMODULE_PATH = '/src/localarena/';
define('APP_GAMEMODULE_PATH', '/src/localarena/');
define('APP_BASE_PATH', '/src/localarena/');

require_once 'modules/php/WcLib/CardBase.php';
require_once 'modules/php/WcLib/Test/IntegrationTestCase.php';

// @phan-suppress-next-line PhanUndeclaredConstant
require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

require_once '/src/localarena/module/tablemanager/TableParams.php';
use \LocalArena\TableParams;

const WCCARD_SCHEMA = <<<END
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,

  -- These two strings uniquely identify the card-type.
  `card_type_group` VARCHAR(32) NOT NULL,
  `card_type` VARCHAR(32) NOT NULL,

  `card_location` VARCHAR(32) NOT NULL,

  -- The library itself supports the well-known "DECK", "DISCARD",
  -- and "HAND" sublocation types; games can add more as appropriate.
  `card_sublocation` VARCHAR(32) NOT NULL,

  -- Similar to `locationArg` from BGA's deck library.  This can be
  -- a character number, a location number on a game board, etc.
  `card_sublocation_index` INT(1),

  -- The order of the card within the (location, sublocation,
  -- sublocation_index) area.  Lower numbers are "first", or closer to
  -- the top of a deck.
  --
  -- Values should be unique.  When they aren't, behavior is
  -- undefined, though we try to use `id` to break ties.
  --
  -- The library may arbitrarily change these values as cards are moved
  -- around (though it won't change relative order unless instructed to).
  `card_order` INT(10) NOT NULL,
END;

abstract class FancyCard extends \WcLib\CardBase
{
  const CARD_TYPE_GROUP = 'FANCY';
}

class BlueFancyCard extends FancyCard {
  const CARD_TYPE = 'BLUE';
}

class RedFancyCard extends FancyCard {
  const CARD_TYPE = 'RED';
}

abstract class BoringCard extends \WcLib\CardBase
{
  const CARD_TYPE_GROUP = 'BORING';
}

class BlueBoringCard extends FancyCard {
  const CARD_TYPE = 'BLUE';
}

class RedBoringCard extends FancyCard {
  const CARD_TYPE = 'RED';
}

Class CardBaseTest extends \WcLib\Test\IntegrationTestCase {
  const LOCALARENA_GAME_NAME = 'localarenanoop';

  function testFoo(): void {
    $params = new TableParams();
    $params->playerCount = 1;
    $params->schema_changes = WCCARD_SCHEMA;
    $this->initTable($params);
  }

  // XXX: Want to test that...

  // - We can construct a {Blue,Red}FancyCard from a database row.

  // - FancyCard::getById() and FancyCard::getAll() work
}
