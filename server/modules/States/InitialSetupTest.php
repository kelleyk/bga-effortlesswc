<?php declare(strict_types=1);

require_once '/src/localarena/module/test/IntegrationTestCase.php';

require_once '/src/server/modules/constants.inc.php';
require_once '/src/server/modules/Test/IntegrationTestCase.php';

class InitialSetupTest extends \EffortlessWC\Test\IntegrationTestCase
{
  protected function setUp(): void
  {
    $this->setupCleanState();
  }

  function testTableSetup(): void
  {
    // XXX: Deliberately empty.
  }
}
