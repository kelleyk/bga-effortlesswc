<?php declare(strict_types=1);

namespace EffortlessWC;

require_once 'config.inc.php';
require_once 'WcLib/BgaTableTrait.php';

// We need to include these so that `visitConcreteSubclasses()` can find subclasses in tests.
require_once 'Models/Setting.php';
require_once 'Models/Location.php';

// This code performs the setup that's done as the table is created.
trait Setup
{
  use \EffortlessWC\BaseTableTrait;

  // XXX: What should happen here and what should happen in ST_INITIAL_SETUP?
  protected function setupNewGame($players, $options = [])
  {
    echo '*** EffortlessWC setupNewGame()'."\n";

    $gameinfos = $this->getGameinfos();

    $this->initPlayers($gameinfos, $players);
    $this->initSeats($gameinfos);

    // Init global game state.  (XXX: Make sure values are correct.)
    $this->setGameStateInt(GAMESTATE_INT_ACTIVE_SEAT, -1);
    $this->setGameStateJson(GAMESTATE_JSON_RESOLVE_STACK, []);
    $this->setGameStateJson(GAMESTATE_JSON_RESOLVE_VALUE_STACK, []);

    $this->initStats();
  }

  // TODO: Init statistics.
  //
  // (note: statistics used in this file must be defined in your stats.inc.php file)
  private function initStats(): void
  {
    //$this->initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
    //$this->initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)
  }

  // XXX: general
  private function visitConcreteSubclasses(string $base_class_name, $cb): void
  {
    foreach (get_declared_classes() as $class) {
      if (is_subclass_of($class, $base_class_name)) {
        $rc = new \ReflectionClass($class);
        if (!$rc->isAbstract()) {
          $cb($rc);
        }
      }
    }
  }

  // XXX: find better home
  public function seatCount(): int
  {
    return max(3, $this->getPlayersNumber());
  }

  private function initPlayers($gameinfos, $players): void
  {
    $default_colors = $gameinfos['player_colors'];

    // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
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

    $this->DbQuery(
      'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ' .
        implode(',', $values)
    );
    $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
    $this->reloadPlayersBasicInfos();
  }

  // Depends on `initPlayers()` having been called.
  private function initSeats($gameinfos): void
  {
    // Set up player seat; assign colors; give 20 effort.
    //
    // TODO: This is based on code borrowed from The Shipwreck Arcana; we could pull a generalized version of this out
    // into WcLib.
    $player_colors = $gameinfos['player_colors'];
    $used_colors = [];
    $values = [];
    foreach ($this->loadPlayersBasicInfos() as $player_id => $player_info) {
      $seat_color = $player_info['player_color'];

      $used_colors[] = $seat_color;
      $values[] = '("' . $player_id . '", "' . $seat_color . '", "", 20)';
    }

    // Set up bot seats when necessary; give 20 effort.
    for ($i = $this->seatCount(); $i < 3; ++$i) {
      $seat_color = array_values(
        array_filter($player_colors, function ($color) use ($used_colors) {
          return !in_array($color, $used_colors);
        })
      )[0];

      $used_colors[] = $seat_color;
      $values[] = '(NULL, "' . $seat_color . '", "", 20)';
    }

    self::DbQuery(
      'INSERT INTO `seat` (`player_id`, `seat_color`, `seat_label`, `reserve_effort`) VALUES ' . implode(',', $values)
    );
  }

  private function initLocationDeck($sets): void
  {
    $card_specs = [];
    $this->visitConcreteSubclasses('EffortlessWC\Location', function ($rc) use (&$card_specs, $sets) {
      if (
        in_array($rc->getConstant('SET_ID'), $sets) &&
        !in_array($rc->getConstant('LOCATION_ID'), DISABLED_LOCATIONS)
      ) {
        $card_specs[] = ['card_type' => $rc->getConstant('LOCATION_ID')];
      }
    });

    $this->locationDeck->createCards($card_specs);
  }

  private function initSettingDeck($sets): void
  {
    $card_specs = [];
    $this->visitConcreteSubclasses('EffortlessWC\Setting', function ($rc) use (&$card_specs, $sets) {
      if (in_array($rc->getConstant('SET_ID'), $sets) && !in_array($rc->getConstant('SETTING_ID'), DISABLED_SETTINGS)) {
        $card_specs[] = ['card_type' => $rc->getConstant('SETTING_ID')];
      }
    });

    $this->settingDeck->createCards($card_specs);
  }

  private function initMainDeck(): void
  {
    $card_specs = [];

    // Create the main deck.  (TODO: Get attribute card counts correct.)
    foreach (['cha', 'con', 'str', 'dex', 'int', 'wis'] as $stat) {
      for ($i = 0; $i < ($this->seatCount() < 4 ? 8 : 10); ++$i) {
        $card_specs[] = ['card_type' => 'attr_' . $stat . '_1'];
      }
      for ($i = 0; $i < ($this->seatCount() < 4 ? 4 : 5); ++$i) {
        $card_specs[] = ['card_type' => 'attr_' . $stat . '_2'];
      }
    }
    for ($i = 0; $i < 21; ++$i) {
      $card_specs[] = ['card_type' => 'item_' . $i];
    }
    foreach (['mage', 'plate', 'leather', 'obsidian'] as $armor_set) {
      $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_head'];
      $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_chest'];
      $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_hands'];
      $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_feet'];
    }
    if ($this->seatCount() >= 4) {
      foreach (['scale', 'assassin'] as $armor_set) {
        $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_head'];
        $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_chest'];
        $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_hands'];
        $card_specs[] = ['card_type' => 'armor_' . $armor_set . '_feet'];
      }
    }

    $this->mainDeck->createCards($card_specs);
  }

  private function fillSetlocs()
  {
    for ($i = 0; $i < 6; ++$i) {
      $this->locationDeck->drawTo('setloc', $i);
      $this->settingDeck->drawTo('setloc', $i);
    }
  }

  // XXX: This shouldn't live here; it will also be called at the end of each turn.
  private function fillSetlocCards()
  {
    foreach ($this->world()->locations() as $loc) {
      $this->world()->fillCards($loc);
    }
  }
}
