<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\ParameterInputConfig;

trait Input
{
  use \EffortlessWC\BaseTableTrait;

  public function stInput()
  {
  }

  public function argInput()
  {
    $paraminput_config = new ParameterInputConfig($this->getGameStateJson(GAMESTATE_JSON_PARAMINPUT_CONFIG));

    return array_merge($this->renderBoardState(), $paraminput_config->state_args, [
      'input' => $paraminput_config->renderForClient($this->world()),
    ]);
  }
}
