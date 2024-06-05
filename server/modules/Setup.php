<?php

namespace EffortlessWC;

require_once('modules/TableBase.php');
require_once('modules/WcLib/BgaTableTrait.php');

// This code performs the setup that's done as the table is created.
trait Setup
{
  use \WcLib\BgaTableTrait;

  protected function setupNewGame($players, $options = [])
  {
    // Set the colors of the players with HTML color code
    // The default below is red/green/blue/orange/brown
    // The number of colors defined here must correspond to the maximum number of players allowed for the gams
    $gameinfos = $this->getGameinfos();
    $default_colors = $gameinfos['player_colors'];

    // Create players
    // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
    $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ';
    $values = [];
    foreach ($players as $player_id => $player) {
      $color = array_shift($default_colors);
      $values[] =
        "('" .
        $player_id .
        "','$color','" .
        $player['player_canal'] .
        "','" .
        addslashes($player['player_name']) .
        "','" .
        addslashes($player['player_avatar']) .
        "')";
    }
    $sql .= implode(',', $values);
    $this->DbQuery($sql);
    $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
    $this->reloadPlayersBasicInfos();

    /************ Start the game initialization *****/

    // Init global values with their initial values
    //$this->setGameStateInitialValue( 'my_first_global_variable', 0 );

    // Init game statistics
    // (note: statistics used in this file must be defined in your stats.inc.php file)
    //$this->initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
    //$this->initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

    // TODO: setup the initial game situation here

    // Activate first player (which is in general a good idea :) )
    $this->activeNextPlayer();

    /************ End of the game initialization *****/
  }
}
