<?php declare(strict_types=1);

trait TableBase {

  abstract public function getGameinfos();

  abstract public function DbQuery(string $query);

  abstract public function reattributeColorsBasedOnPreferences($players, $colors);

  abstract public function reloadPlayersBasicInfos();

  abstract protected function activeNextPlayer();
}
