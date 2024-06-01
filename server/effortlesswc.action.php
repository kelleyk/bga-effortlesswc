<?php

 // WARNING: THIS FILE HAS BEEN AUTOMATICALLY GENERATED. ANY CHANGES MADE DIRECTLY MAY BE OVERWRITTEN.

require_once('module/table/table.game.php');

class action_effortlesswc extends APP_GameAction
{
  /** @var effortlesswc $game */
  protected $game; // Enforces functions exist on Table class

  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = 'common_notifwindow';
      $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
    } else {
      $this->view = 'effortlesswc_effortlesswc';
      self::trace('Complete reinitialization of board game');
    }
  }

  public function playCard()
  {
    self::setAjaxMode();

    /** @var int $card_id */
    $card_id = self::getArg('card_id', AT_int, true);

    $this->game->playCard($card_id);
    self::ajaxResponse();
  }

  public function pass()
  {
    self::setAjaxMode();

    $this->game->pass();
    self::ajaxResponse();
  }
}
