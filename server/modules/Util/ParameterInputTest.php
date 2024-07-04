<?php declare(strict_types=1);

// XXX: I need to split ParameterInput into a WcLib base class and a game-specific child class, and move the tests to
// WcLib.

require_once 'modules/php/constants.inc.php';
require_once 'modules/php/Test/IntegrationTestCase.php';

class ParameterInputTest extends \EffortlessWC\Test\IntegrationTestCase
{
  protected function setUp(): void
  {
    $this->setupCleanState();
  }

  function testTableSetup(): void
  {
    $this->assertGameState(ST_PLACE_EFFORT);

    // XXX: Deliberately empty.
  }
}
