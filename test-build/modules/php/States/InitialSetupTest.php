<?php declare(strict_types=1);

require_once '/src/localarena/module/test/IntegrationTestCase.php';

require_once 'modules/php/constants.inc.php';
require_once 'modules/php/Test/IntegrationTestCase.php';

class InitialSetupTest extends \EffortlessWC\Test\IntegrationTestCase
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
