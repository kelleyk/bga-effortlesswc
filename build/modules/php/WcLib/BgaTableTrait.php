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

  /**
    @return void
  */
  abstract protected function activeNextPlayer();

  // XXX: These are actually on $table->gamestate

  // /**
  //   @return void
  // */
  // abstract protected function activePrevPlayer();

  // /**
  //   @return void
  // */
  // abstract protected function changeActivePlayer(string $player_id);

  // /**
  //   @return void
  // */
  // abstract protected function setAllPlayersMultiactive();

  // /**
  //   N.B.: $next_state is actually a transition name.

  //   @return void
  // */
  // abstract protected function setAllPlayersNonMultiactive(string $next_state);

  // /**
  //   N.B.: $next_state is actually a transition name.

  //   @param string[] $players
  //   @return void
  // */
  // abstract protected function setPlayersMultiactive($players, string $next_state, boolean $bExclusive = false);

  // /**
  //   N.B.: $next_state is actually a transition name.

  //   @param string[] $players
  //   @return void
  // */
  // abstract protected function setPlayersMultiactive($players, string $next_state);

  // /*
  //   // XXX: Add all of these to interface.

  //   $this->activeNextPlayer()
  //   $this->activePrevPlayer()
  //   $this->gamestate->changeActivePlayer( $player_id )
  //   $this->gamestate->setAllPlayersMultiactive()
  //   $this->gamestate->setAllPlayersNonMultiactive( $next_state )
  //   $this->gamestate->setPlayersMultiactive( $players, $next_state, $bExclusive = false )
  //   $this->gamestate->setPlayerNonMultiactive( $player_id, $next_state )
  //  */

  // XXX: Returns int, but the PHP 8 version of BGA Studio defines this with a different signature.
  abstract public function getPlayersNumber();

  // Returns "prod" in production and "studio" in BGA Studio.
  /** @return string */
  abstract public static function getBgaEnvironment();

  // APP_DbObject
  //
  // Some of these are static-qualified and some aren't; which BGA itself does seems to be arbitrary.

  abstract public static function DbQuery(string $sql);

  abstract public static function getUniqueValueFromDB(string $sql);

  // XXX: YYY(method-staticness): This is not currently static in LocalArena; changing it to non-static to get local
  // tests to run, but I wonder if that will break when we upload to BGA Studio.
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
  // BGA game-state

  //  Initialize global value. This is not required if you ok with default value if 0. This should be called from
  //  setupNewGame function.
  /**
    @param string $label
    @param int $value
    @return void
   */
  abstract public function setGameStateInitialValue($label, $value );

  // Retrieve the value of a global. Returns $default if global is not been initialized (by setGameStateInitialValue).
  //
  // NOTE: this method use globals "cache" if you directly manipulated globals table OR call this function after undoRestorePoint() - it won't work as expected.
  //
  //   $value = $this->getGameStateValue('my_first_global_variable');
  //
  // For debugging purposes, you can have labels and value pairs send to client side by inserting that code in your "getAllDatas":
  //
  // $labels = array_keys($this->mygamestatelabels);
  // $result['myglobals'] = array_combine($labels, array_map([$this,'getGameStateValue'],$labels));
  //
  // That assumes you stored your label mapping in $this->mygamestatelabels in constructor
  //
  //   $this->mygamestatelabels=["my_first_global_variable" => 10, ...];
  //   $this->initGameStateLabels($this->mygamestatelabels);
  /**
    @param string $label
    @param int $default
    @return int
   */
  abstract public function getGameStateValue($label, $default = 0);

  // Set the current value of a global.
  //
  //  $this->setGameStateValue('my_first_global_variable', 42);
  /**
    @param string $label
    @param int $value
    @return void
   */
  abstract public function setGameStateValue($label, $value);

  // Increment the current value of a global. If increment is negative, decrement the value of the global.
  //
  // Return the final value of the global. If global was not initialized it will initialize it as 0.
  //
  // NOTE: this method use globals "cache" if you directly manipulated globals table OR call this function after undoRestorePoint() - it won't work as expected.
  //
  //   $value = $this->incGameStateValue('my_first_global_variable', 1);
  /**
    @param string $label
    @param int $increment
    @return void
   */
  abstract public function incGameStateValue($label, $increment);

  // -----
  // State-machine

  /**
    @param string $action
    @param bool $nomessage
    @return void
   */
  abstract public function checkAction($action, $nomessage = false);

  // -----
  // Notifs

  /**
    @param string $notif_type
    @param string $message
    @param mixed[] $args
    @return void
  */
  abstract public function notifyAllPlayers($notif_type, $message, $args);


  // -----
}
