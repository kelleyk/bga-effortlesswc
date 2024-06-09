<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait PlaceEffort
{
  use \WcLib\BgaTableTrait;

  public function stPlaceEffort()
  {
  }

  public function argPlaceEffort()
  {
    echo '*** player' . "\n";
    print_r($this->getCollectionFromDB('SELECT player_id, player_name FROM player WHERE TRUE'), true);
    echo '*** seats' . "\n";
    print_r($this->getCollectionFromDB('SELECT id, seat_color, seat_label FROM seat WHERE TRUE'), true);
    echo '*** location cards' . "\n";
    // card_location = "location"
    print_r($this->getCollectionFromDB('SELECT id, card_type_group, card_type FROM card WHERE TRUE'), true);
    // foreach ($this->locationDeck->getAll() as $card) {
    //   echo $card . "\n";
    // }
    echo '*** location cards end' . "\n";

    // XXX: temporary
    return [
        // 'players' => $this->getCollectionFromDB('SELECT * FROM player WHERE TRUE'),
        // 'seats' => $this->getCollectionFromDB('SELECT * FROM seat WHERE TRUE'),
        // 'cards' => $this->getCollectionFromDB('SELECT * FROM card WHERE TRUE'),
      ];
  }
}
