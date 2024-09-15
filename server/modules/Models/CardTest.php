<?php declare(strict_types=1);

require_once '/src/localarena/module/test/IntegrationTestCase.php';

require_once 'modules/php/constants.inc.php';
require_once 'modules/php/Test/IntegrationTestCase.php';

class CardTest extends \Effortless\Test\IntegrationTestCase
{
  protected function setUp(): void
  {
    $this->setupCleanState();
  }

  function testTableSetup(): void
  {
    $this->assertGameState(ST_INPUT);

    // XXX: Deliberately empty.
  }

  // XXX: test parsing from database for each card type, and both face-up and face-down renderForClient()
}
