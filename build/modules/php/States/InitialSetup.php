<?php declare(strict_types=1);

namespace Effortless\States;

trait InitialSetup
{
  use \Effortless\BaseTableTrait;
  use \Effortless\Setup;

  public function stInitialSetup()
  {
    // XXX: Eventually, this should be driven by a game option.
    $sets = [
      SET_BASE,
      // SET_ALTERED,
      // SET_HUNTED,
    ];

    $this->initMainDeck();
    $this->initLocationDeck($sets);
    $this->initSettingDeck($sets);

    $this->fillSetlocs();
    //throw new \feException('XXX: 02 - stinitialsetup');
    $this->fillSetlocCards();
    //throw new \feException('XXX: 03 - stinitialsetup');

    // N.B.: This needs to happen after `fillSetlocs()` and after `initSeats()`.
    $this->initEffortPiles();

    // We're all set!  Transition to ST_NEXT_TURN.
    $this->world()->nextState(T_DONE);
    //throw new \feException('XXX: 04 - stinitialsetup');
  }
}
