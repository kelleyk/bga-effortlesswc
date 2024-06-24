<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Models\Seat;
use EffortlessWC\Models\Player;

trait PlaceEffort
{
  use \EffortlessWC\BaseTableTrait;

  public function stPlaceEffort()
  {
  }

  public function argPlaceEffort()
  {
    // echo '*** player' . "\n";
    // print_r($this->getCollectionFromDB('SELECT player_id, player_name FROM player WHERE TRUE'), true);
    // echo '*** seats' . "\n";
    // print_r($this->getCollectionFromDB('SELECT id, seat_color, seat_label FROM seat WHERE TRUE'), true);
    // echo '*** location cards' . "\n";
    // // card_location = "location"
    // print_r($this->getCollectionFromDB('SELECT id, card_type_group, card_type FROM card WHERE TRUE'), true);
    // // foreach ($this->locationDeck->getAll() as $card) {
    // //   echo $card . "\n";
    // // }
    // echo '*** location cards end' . "\n";

    // XXX: temporary
    $world = $this->world();
    return [
      'players' => $this->renderForClient(Player::getAll($world)),
      'seats' => $this->renderForClient(Seat::getAll($world)),
      'cards' => $this->renderForClient($this->mainDeck->getAll(['SETLOC', 'DISCARD'])),
      'locations' => $this->renderForClient($this->locationDeck->getAll(['SETLOC'])),
      'settings' => $this->renderForClient($this->settingDeck->getAll(['SETLOC'])),
    ];
  }
}
