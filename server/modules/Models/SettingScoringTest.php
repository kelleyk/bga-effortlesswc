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

/*
- name: "Active"
  text: "The player with the most effort here gains 4 points."
- name: "Crowded"
  text: "Each player with at least 5 effort here gains 10 points."
- name: "Lively"
  text: "Each player gains 1 point for each effort they have here."
- name: "Peaceful"
  text: "Each player gains 3 points for every 2 effort they have here."
- name: "Battling"
  text: "The player with the most effort here gains 8 points."
- name: "Barren"
  text: "(No effect.)"
- name: "Hidden"
  text: "The player with the least effort here loses 5 points."
- name: "Treacherous"
  text: "Each player loses 1 point for each effort they have here."
- name: "Quiet"
  text: "The player with the most effort here loses 5 points."
- name: "Eerie"
  text: "The player with the least effort here gains 5 points."
- name: "Holy"
  text: "The player with the most effort here gains 2 points for each effort."
- name: "Ghostly"
  text: "Each player loses 2 points for every 2 effort here."
- name: "Frozen"
  text: "Once all players have placed half of their effort, this setting will be replaced at random."
*/

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
  function doTestSettingScoring(string $setting_name, $effort, $expected_score): void
  {
    $world = $this->table()->world();

    $setloc = $this->setlocByPos(0);
    $setloc->setLocation('location:wasteland');
    $setloc->setSetting('setting:active');

    foreach ($setloc->effortPiles() as $pile) {
      $pile->setQty($effort[$pile->seatId() - 1]);
    }

    $table_scores = \Effortless\calculateScores($this->table()->world());

    foreach ($setloc->effortPiles() as $pile) {
      $score = $table_scores->by_seat[$pile->seatId()]->setting[$setloc->locationId()];
      $this->assertEquals($expected_score[$pile->seatId() - 1], $score);
    }
  }

  // "The player with the most effort here gains 4 points."
  function testActive(): void
  {
    $this->doTestSettingScoring('active', [0, 0, 0], [0, 0, 0]);
  }
}
