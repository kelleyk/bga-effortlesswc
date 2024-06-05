<?php declare(strict_types=1);

// N.B.: This resolves the undefined-member errors that Phan would otherwise throw when we call BGA API entry points.
//
// If we need to add more things to this, VictoriaLa's stubs are a great place to look for rough signatures.
//
trait TableBase {

  abstract public function getGameinfos();

  abstract public function DbQuery(string $query);

  abstract public function reattributeColorsBasedOnPreferences($players, $colors);

  abstract public function reloadPlayersBasicInfos();

  abstract protected function activeNextPlayer();
}
