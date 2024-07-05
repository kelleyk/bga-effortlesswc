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
      'input_description' => $paraminput_config->description,
      'input_descriptionmyturn' => $paraminput_config->descriptionmyturn,
      'input_cancellable' => $paraminput_config->cancellable,
      'input_choices' => $paraminput_config->choices,
      'input_type' => $paraminput_config->expected_type,
    ]);
  }
}
