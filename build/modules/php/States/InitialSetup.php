<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait InitialSetup
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\Setup;

  public function stInitialSetup()
  {
    $sets = [SET_BASE, SET_ALTERED, SET_HUNTED];

    $this->initMainDeck();
    $this->initLocationDeck($sets);
    $this->initSettingDeck($sets);

    // echo '*** player' . "\n";
    // print_r($this->getCollectionFromDB('SELECT * FROM player WHERE TRUE'), true);
    // echo '*** seats' . "\n";
    // print_r($this->getCollectionFromDB('SELECT * FROM seat WHERE TRUE'), true);
    // echo '*** location cards' . "\n";
    // // card_location = "location"
    // print_r($this->getCollectionFromDB('SELECT * FROM card WHERE TRUE'), true);
    // // foreach ($this->locationDeck->getAll() as $card) {
    // //   echo $card . "\n";
    // // }
    // echo '*** location cards end' . "\n";

    $this->fillSetlocs();
    $this->fillSetlocCards();

    // We're all set!  Transition to ST_NEXT_TURN.
    $this->world()->nextState(T_DONE);
  }
}
