<?php declare(strict_types=1);

// XXX: I need to split ParameterInput into a WcLib base class and a game-specific child class, and move the tests to
// WcLib.

require_once 'modules/php/constants.inc.php';
require_once 'modules/php/Test/IntegrationTestCase.php';

class ParameterInputTest extends \Effortless\Test\IntegrationTestCase
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

  /*
    Test that:

    - When we hit the first "input point":
      next-read-index=0
      input-config param-index=0
      next-write-index=1

    - Imagine that we have code that asks for two inputs.  When the first one has been provided, the value-stack should
      have a single entry (index 0), the next-read-index should be 0, and the next-write-index should be 2.  The param-index in      the input-config should be 1.

    - When we provide the second input, the value-stack should be empty again, and both indices should be 2.

    Invariants:

    - When we are in ST_INPUT, the next-read-index must be <= the input-config's param index.  (Otherwise we'll never
      read it!)

    - When we are in ST_INPUT, the next-write-index must be one more than the input-config's param index.

    - The next-write-index must always be >= the next-read-index.
   */

  /*
    — so we start with

    * [], read 0, write 0, ici NULL

    Then we hit our first input point.  We attempt to read 0, see that it is not available, and we go to ST_INPUT.  We
    consume write-index 0.  Reverting restores stack=[], read=0.

    * [], read 0, write 1, ici 0


   */
}
