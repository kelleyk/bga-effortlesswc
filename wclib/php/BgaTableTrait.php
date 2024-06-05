<?php declare(strict_types=1);

namespace WcLib;

// N.B.: This resolves the undefined-member errors that Phan would otherwise throw when we call BGA API entry points.
//
// If we need to add more things to this, VictoriaLa's stubs are a great place to look for rough signatures.
//
trait BgaTableTrait {

  abstract public function getGameinfos();

  abstract public function reattributeColorsBasedOnPreferences($players, $colors);

  abstract public function reloadPlayersBasicInfos();

  abstract protected function activeNextPlayer();

  // APP_DbObject

  abstract public function DbQuery(string $sql);

  abstract public function getUniqueValueFromDB(string $sql);

  abstract public function getCollectionFromDB(string $query, bool $single = false);

  abstract public function getNonEmptyCollectionFromDB(string $sql);

  abstract public function getObjectFromDB(string $sql);

  abstract public function getNonEmptyObjectFromDB(string $sql);

  abstract public function getObjectListFromDB(string $query, bool $single = false);

  abstract public function getDoubleKeyCollectionFromDB(string $sql, bool $bSingleValue = false);

  abstract public function DbGetLastId();

  abstract public function DbAffectedRow();

  abstract public function escapeStringForDB(string $string);

  // -----
}
