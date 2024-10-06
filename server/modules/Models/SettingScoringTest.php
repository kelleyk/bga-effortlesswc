<?php declare(strict_types=1);

require_once '/src/localarena/module/test/IntegrationTestCase.php';

require_once 'modules/php/constants.inc.php';
require_once 'modules/php/Test/IntegrationTestCase.php';

require_once LOCALARENA_GAME_PATH . 'effortless/modules/php/Scoring.php';

/*
There are the actual scoring bugs that I'm aware of (but can't find my notes about).

  - 118/market: presence (2,0,1); seat 1 only gets 1 point

  - 112/cave: presence (0,1,0); seat 2 gets -1 point but should get 0

  - 116/crypt: presence (0,1,1); scoring of (0,3,3) seems correct

  - 120/coliseum: presence (1,0,0); scoring of (-3,0,0) seems correct
*/

// use LocalArena\TableParams;

final class SettingScoringTest extends \Effortless\Test\IntegrationTestCase
{
  protected function setUp(): void
  {
    $this->setupCleanState();
  }

  /**
    @param $effort int[]
    @param $expected_score int[]
  **/
  function doTestSettingScoring(string $label, string $setting_name, $effort, $expected_score): void
  {
    $world = $this->table()->world();

    $setloc = $this->setlocByPos(0);
    $setloc->setLocation('location:wasteland');
    $setloc->setSetting('setting:' . $setting_name);

    foreach ($setloc->effortPiles() as $pile) {
      $pile->setQty($effort[$pile->seatId() - 1]);
    }

    $table_scores = \Effortless\calculateScores($this->table()->world());

    echo $setloc->location()->debugString() . "\n";
    echo $setloc->setting()->debugString() . "\n";

    foreach ($setloc->effortPiles() as $pile) {
      $score = $table_scores->by_seat[$pile->seatId()]->setting[$setloc->locationId()];

      $expected_seat_score = $expected_score[$pile->seatId() - 1];
      $this->assertEquals(
        $expected_seat_score,
        $score,
        '[Case ' .
          $label .
          ']: Expected seat ' .
          $pile->seatId() .
          ' to score ' .
          $expected_seat_score .
          ' points, but instead they scored ' .
          $score .
          '.'
      );
    }
  }

  // "Each player with at least 1 effort here gains 3 points."
  function testActive(): void
  {
    $this->doTestSettingScoring('#0', 'active', [0, 2, 1], [0, 3, 3]);
  }

  // "Each player with at least 5 effort here gains 10 points."
  function testCrowded(): void
  {
    $this->doTestSettingScoring('#0', 'crowded', [0, 4, 3], [0, 0, 0]);
    $this->doTestSettingScoring('#1', 'crowded', [5, 4, 3], [10, 0, 0]);
    $this->doTestSettingScoring('#2', 'crowded', [5, 4, 6], [10, 0, 10]);
  }

  // "Each player gains 1 point for each effort they have here."
  function testLively(): void
  {
    $this->doTestSettingScoring('#0', 'lively', [0, 4, 3], [0, 4, 3]);
    $this->doTestSettingScoring('#1', 'lively', [1, 2, 1], [1, 2, 1]);
  }

  // "Each player gains 3 points for every 2 effort they have here."
  function testPeaceful(): void
  {
    $this->doTestSettingScoring('#0', 'peaceful', [0, 4, 3], [0, 6, 3]);
    $this->doTestSettingScoring('#1', 'peaceful', [1, 2, 0], [0, 3, 0]);
  }

  // "The player with the most effort here gains 8 points."
  function testBattling(): void
  {
    // The rulebook says
    // - "If 2 or more players are tied for the most or least Effort at a location, they all score the Setting."
    // - "Having 0 Effort at a Location DOES count as having the least."
    $this->doTestSettingScoring('#0', 'battling', [0, 0, 0], [8, 8, 8]);

    $this->doTestSettingScoring('#1', 'battling', [0, 0, 1], [0, 0, 8]);
    $this->doTestSettingScoring('#2', 'battling', [0, 1, 1], [0, 8, 8]);
    $this->doTestSettingScoring('#3', 'battling', [0, 2, 1], [0, 8, 0]);
  }

  // "(No effect.)"
  function testBarren(): void
  {
    $this->doTestSettingScoring('#0', 'barren', [0, 0, 0], [0, 0, 0]);
    $this->doTestSettingScoring('#1', 'barren', [0, 4, 3], [0, 0, 0]);
  }

  // "The player with the least effort here loses 5 points."
  function testHidden(): void
  {
    $setting_name = 'hidden';

    $i = 0;
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 0, 0], [-5, -5, -5]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 1, 2], [-5, 0, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [3, 1, 2], [0, -5, 0]);
  }

  // "Each player loses 1 point for each effort they have here."
  function testTreacherous(): void
  {
    $setting_name = 'treacherous';

    $i = 0;
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 0, 0], [0, 0, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 1, 2], [0, -1, -2]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [3, 1, 2], [-3, -1, -2]);
  }

  // "The player with the most effort here loses 3 points."
  function testQuiet(): void
  {
    $setting_name = 'quiet';

    $i = 0;
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 0, 0], [-3, -3, -3]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 1, 0], [0, -3, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [2, 1, 0], [-3, 0, 0]);
  }

  // "The player with the least effort here gains 5 points."
  function testEerie(): void
  {
    $setting_name = 'eerie';

    $i = 0;
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 0, 0], [3, 3, 3]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 1, 0], [3, 0, 3]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [2, 1, 0], [0, 0, 3]);
  }

  // "The player with the most effort here gains 1 point for each effort."
  function testHoly(): void
  {
    $setting_name = 'holy';

    $i = 0;
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 0, 0], [0, 0, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [4, 7, 0], [0, 7, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [4, 0, 0], [4, 0, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [3, 0, 3], [3, 0, 3]);
  }

  // "Each player loses 2 points for every 2 effort here."
  function testGhostly(): void
  {
    $setting_name = 'ghostly';

    $i = 0;
    $this->doTestSettingScoring('#' . $i++, $setting_name, [0, 0, 0], [0, 0, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [4, 7, 0], [0, -7, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [4, 1, 0], [-4, 0, 0]);
    $this->doTestSettingScoring('#' . $i++, $setting_name, [3, 0, 3], [-3, 0, -3]);
  }
}
