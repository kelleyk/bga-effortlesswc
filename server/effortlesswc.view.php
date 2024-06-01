<?php
/**
 * expressionswc.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in emptygame_emptygame.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 */

// @phan-suppress-next-line PhanUndeclaredConstant
require_once APP_BASE_PATH . 'view/common/game.view.php';

class view_expressionswc_expressionswc extends game_view
{
  function getGameName()
  {
    return 'expressionswc';
  }

  function build_page($viewArgs)
  {
  }
}
