<?php declare(strict_types=1);

namespace Effortless;

require 'Scoring.php';

trait DebugTrait
{
  use \Effortless\BaseTableTrait;

  public function loadBugReportSQL(int $reportId, array $studioPlayers): void
  {
    $prodPlayers = $this->getObjectListFromDb('SELECT `player_id` FROM `player`', true);
    $prodCount = count($prodPlayers);
    $studioCount = count($studioPlayers);
    if ($prodCount != $studioCount) {
      throw new \BgaVisibleSystemException(
        "Incorrect player count (bug report has $prodCount players, studio table has $studioCount players)"
      );
    }

    $queries = [];

    // SQL specific to your game

    // // For example, reset the current state if it's already game over
    // $queries = [
    //     "UPDATE `global` SET `global_value` = 10 WHERE `global_id` = 1 AND `global_value` = 99"
    // ];
    foreach ($prodPlayers as $index => $prodId) {
      $studioId = $studioPlayers[$index];

      // SQL common to all games.
      $queries[] = "UPDATE `player` SET `player_id` = $studioId WHERE `player_id` = $prodId";
      $queries[] = "UPDATE `global` SET `global_value` = $studioId WHERE `global_value` = $prodId";
      $queries[] = "UPDATE `stats` SET `stats_player_id` = $studioId WHERE `stats_player_id` = $prodId";

      // SQL specific to your game.  You need to update any place that might store a player ID.
      $queries[] = "UPDATE `seat` SET `player_id` = $studioId WHERE `player_id` = $prodId";
    }
    foreach ($queries as $query) {
      $this->DbQuery($query);
    }
    $this->reloadPlayersBasicInfos();
  }

  public function debug_calculateScores()
  {
    $scoring = calculateScores($this->world());

    $this->world()
      ->table()
      ->notifyAllPlayers('XXX_debug', 'Current scoring: ' . json_encode($scoring), []);
  }
}
