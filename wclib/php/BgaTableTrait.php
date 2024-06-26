<?php declare(strict_types=1);

namespace WcLib;

// N.B.: This resolves the undefined-member errors that Phan would otherwise throw when we call BGA API entry points.
//
// If we need to add more things to this, VictoriaLa's stubs are a great place to look for rough signatures.
//
trait BgaTableTrait {

  // XXX: Improve typing.
  public $gamestate;

  abstract public function getGameinfos();

  abstract public function reattributeColorsBasedOnPreferences($players, $colors);

  abstract public function loadPlayersBasicInfos();

  abstract public function reloadPlayersBasicInfos();

  abstract protected function activeNextPlayer();

  // XXX: Returns int, but the PHP 8 version of BGA Studio defines this with a different signature.
  abstract public function getPlayersNumber();

  /** @return string */
  abstract public static function getBgaEnvironment();

  // APP_DbObject
  //
  // Some of these are static-qualified and some aren't; which BGA itself does seems to be arbitrary.

  abstract public static function DbQuery(string $sql);

  abstract public static function getUniqueValueFromDB(string $sql);

  abstract public function getCollectionFromDB(string $query, bool $single = false);

  abstract public function getNonEmptyCollectionFromDB(string $sql);

  abstract public function getObjectFromDB(string $sql);

  abstract public function getNonEmptyObjectFromDB(string $sql);

  abstract public static function getObjectListFromDB(string $query, bool $single = false);

  abstract public function getDoubleKeyCollectionFromDB(string $sql, bool $bSingleValue = false);

  abstract public static function DbGetLastId();

  // @returns int
  abstract public static function DbAffectedRow();

  abstract public static function escapeStringForDB(string $string);

  // -----
}
