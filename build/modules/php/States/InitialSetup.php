<?php declare(strict_types=1);

namespace EffortlessWC\States;

trait InitialSetup
{
  use \EffortlessWC\BaseTableTrait;
  use \EffortlessWC\Setup;

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
    $this->fillSetlocCards();

    // We're all set!  Transition to ST_NEXT_TURN.
    $this->world()->nextState(T_DONE);
  }
}
