<?php declare(strict_types=1);

require_once '/src/localarena/module/test/IntegrationTestCase.php';

require_once 'modules/php/constants.inc.php';
require_once 'modules/php/Test/IntegrationTestCase.php';

// use LocalArena\TableParams;

// use \Effortless\Test\IntegrationTestCase as X;
// use \LocalArena\Test\IntegrationTestCase as Y;

final class DocksLocationTest extends \Effortless\Test\IntegrationTestCase
{
  protected function setUp(): void
  {
    $this->setupCleanState();
  }

  // Regression test: visiting the "Ghostly Docks" when there was no effort on it caused an error: "Unexpected error:
  // Internal error: value-stack is not empty in ST_NEXT_TURN."
  function testVisitWithoutEffort(): void
  {
    $setloc = $this->setlocByPos(0);
    $setloc->setLocation('location:docks'); // XXX: This is a CARD_TYPE.  Is there a better const or similar to use?
    $setloc->setSetting('setting:ghostly'); // XXX: I don't think this actually matters for reproducing the bug.

    $seat = $this->activeSeat();
    $pile = $setloc->effortPileBySeat($seat);

    // This should always be the initial state when we start a test case, but let's double-check since this is important
    // for reproducing the bug.
    $this->assertEquals(0, $pile->qty());

    // Okay, now place an effort on this setloc.
    $seat->actVisit($setloc);

    // We should not be asked to move the effort we've just placed (because the rules state that you can never move that
    // effort).  We should not have been asked for input; it should be somebody else's turn.
    $this->assertNotEquals($seat->id(), $this->activeSeat()->id());
    $this->assertEquals(1, $pile->qty());
  }
}
