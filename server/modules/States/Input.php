<?php declare(strict_types=1);

namespace EffortlessWC\States;

use EffortlessWC\Util\ParameterInputConfig;

use EffortlessWC\Models\EffortPile;
use EffortlessWC\Models\Location;
use EffortlessWC\Models\Card;

trait Input
{
  use \EffortlessWC\BaseTableTrait;

  public function stInput()
  {
  }

  public function getParamInputConfig(): ParameterInputConfig
  {
    return new ParameterInputConfig($this->getGameStateJson(GAMESTATE_JSON_PARAMINPUT_CONFIG));
  }

  public function argInput()
  {
    $paraminput_config = $this->getParamInputConfig();

    return array_merge($this->renderBoardState(), $paraminput_config->state_args, [
      'input' => $paraminput_config->renderForClient($this->world()),
    ]);
  }

  // XXX: This structure will make it harder for us to do multiple-value inputs, which we have needed to do in e.g. The
  // Shipwreck Arcana.  We should come back and generalize this (to e.g. accept an array of selections).
  /**
    @param mixed[] $selection
  */
  public function onActSelectInput_stInput($selection): void
  {
    $paraminput_config = $this->getParamInputConfig();

    // XXX: Validate $selection.  It is an object and contains {
    //   - "inputType"
    //   - "value"
    // }
    $selection_input_type = $selection['inputType'] ?? '';
    if ($paraminput_config->input_type !== $selection_input_type) {
      throw new \BgaVisibleSystemException(
        'Selection (type "' .
          $selection_input_type .
          '") does not match expected input type ("' .
          $paraminput_config->input_type .
          '").'
      );
    }
    $raw_value = $selection['value'] ?? null;
    if ($raw_value === null) {
      throw new \BgaVisibleSystemException('Selection does not contain a value.');
    }

    switch ($paraminput_config->input_type) {
      case INPUTTYPE_LOCATION:
        $value = intval($raw_value);

        if (!in_array($value, $paraminput_config->choices)) {
          throw new \BgaUserException('Invalid target (location with ID=' . $value . ').');
        }
        $location = Location::mustGetById($this->world(), $value);

        $this->valueStack->push([
          'paramIndex' => $paraminput_config->param_index,
          'valueType' => INPUTTYPE_LOCATION,
          'value' => $location->id(),
          'sourceType' => 'USER_INPUT',
          //
          // XXX: I don't think we need this without a resolve-stack.
          // 'productionDepth' => $stack_depth,
          //
          // XXX: Do we still need this?
          // 'sourceType' => 'TARGET_SELECTION',
          //
        ]);
        $this->world()->nextState($paraminput_config->return_transition);
        break;
      case INPUTTYPE_EFFORT_PILE:
        $value = intval($raw_value);

        if (!in_array($value, $paraminput_config->choices)) {
          throw new \BgaUserException('Invalid target (effort pile with ID=' . $value . ').');
        }
        $pile = EffortPile::mustGetById($this->world(), $value);

        $this->valueStack->push([
          'paramIndex' => $paraminput_config->param_index,
          'valueType' => INPUTTYPE_EFFORT_PILE,
          'value' => $pile->id(),
          'sourceType' => 'USER_INPUT',
          //
          // XXX: I don't think we need this without a resolve-stack.
          // 'productionDepth' => $stack_depth,
          //
          // XXX: Do we still need this?
          // 'sourceType' => 'TARGET_SELECTION',
          //
        ]);
        $this->world()->nextState($paraminput_config->return_transition);
        break;

      case INPUTTYPE_CARD:
        $value = intval($raw_value);

        // N.B.: This is necessary because we store the rendered-for-client card, and not only its ID, in the config.
        // We could revert that change (again storing the ID) and instead make the arg-rendering code smart enough to
        // render the cards as they're given to the client, if we wanted.
        $valid_choices = array_values(
          array_map(function ($rendered_card) {
            return $rendered_card['id'];
          }, $paraminput_config->choices)
        );

        if (!in_array($value, $valid_choices)) {
          throw new \BgaUserException(
            'Invalid target (card with ID=' . $value . ').  Valid choices: ' . print_r($valid_choices, true)
          );
        }
        $card = Card::mustGetById($this->world(), $value);

        $this->valueStack->push([
          'paramIndex' => $paraminput_config->param_index,
          'valueType' => INPUTTYPE_CARD,
          'value' => $card->id(),
          'sourceType' => 'USER_INPUT',
          //
          // XXX: I don't think we need this without a resolve-stack.
          // 'productionDepth' => $stack_depth,
          //
          // XXX: Do we still need this?
          // 'sourceType' => 'TARGET_SELECTION',
          //
        ]);
        $this->world()->nextState($paraminput_config->return_transition);
        break;

      default:
        throw new \BgaVisibleSystemException(
          'Internal error: unexpected `inputType` in ST_INPUT: ' . $paraminput_config->input_type
        );
    }

    // validate that $selected is an input of the correct type and that it appears in the set of valid choices

    // push it onto the value stack

    // state transition
  }

  // -------
  // XXX: This commented-out stuff is the original logic from Burgle Bros 2; it's here to use as a template.
  // -------

  //   function onActSelectTarget_stTargetSelection($params)
  // {
  //   $world = $this;

  //   $metadata = $this->getGameStateJson(GAMESTATE_JSON_TARGET_SELECTION);

  //   // Check that the type is correct and that the selection is
  //   // among the set of valid choices we were given.

  //   $stack_depth = count($world->getGameStateJson(GAMESTATE_JSON_RESOLVE_STACK)) + 1; // XXX: Duplicate read.

  //   switch ($metadata['valueType']) {
  //     case 'TILE':
  //       $pos = Position::fromArray($params['tile']);
  //       if ($pos === null) {
  //         throw new \BgaUserException('Invalid target (null).');
  //       }
  //       if (!in_array($pos->toArray(), $metadata['choices'])) {
  //         throw new \BgaUserException('Invalid target (tile at ' . $pos . ').');
  //       }

  //       $this->pushOnResolveValueStack([
  //         'valueType' => 'TILE',
  //         'productionDepth' => $stack_depth,
  //         'targetIdx' => $metadata['targetIdx'],
  //         'sourceType' => 'TARGET_SELECTION',
  //         'pos' => $pos->toArray(),
  //       ]);
  //       $this->nextState('tResolveEffects');
  //       break;

  //     case 'ENTITY':
  //       if (!array_key_exists('entity', $params)) {
  //         throw new \BgaUserException('Invalid target (key not present).');
  //       }
  //       $entity_id = intval($params['entity']);
  //       if (!in_array($entity_id, $metadata['choices'])) {
  //         throw new \BgaUserException('Invalid target (entity ID ' . $entity_id . ').');
  //       }

  //       $this->pushOnResolveValueStack([
  //         'valueType' => 'ENTITY',
  //         'productionDepth' => $stack_depth,
  //         'targetIdx' => $metadata['targetIdx'],
  //         'sourceType' => 'TARGET_SELECTION',
  //         'entity' => $entity_id,
  //       ]);
  //       $this->nextState('tResolveEffects');
  //       break;

  //     // TODO: Implement WALL and possibly PC.

  //     case 'CUSTOM':
  //       $value = $params['customValue'];
  //       if (
  //         !in_array(
  //           $value,
  //           array_map(function ($choice) {
  //             return $choice['value'];
  //           }, $metadata['choices'])
  //         )
  //       ) {
  //         throw new \BgaUserException('Invalid target.');
  //       }

  //       $this->pushOnResolveValueStack([
  //         'valueType' => 'CUSTOM',
  //         'productionDepth' => $stack_depth,
  //         'targetIdx' => $metadata['targetIdx'],
  //         'sourceType' => 'TARGET_SELECTION',
  //         'customValue' => $value,
  //       ]);
  //       $this->nextState('tResolveEffects');
  //       break;
  //     default:
  //       throw new \BgaVisibleSystemException(
  //         'Internal error: unexpected valueType in ST_TARGET_SELECTION: ' . $metadata['valueType']
  //       );
  //   }
  // }

  //   function onActCancel_stTargetSelection()
  // {
  //   $world = $this;

  //   $metadata = $this->getGameStateJson(GAMESTATE_JSON_TARGET_SELECTION);
  //   if (!($metadata['cancellable'] ?? true)) {
  //     throw new \BgaUserException('Cannot cancel this target selection.');
  //   }

  //   // Remove any values on the resolve-value stack that are there
  //   // from previous selections related to the same effect.
  //   $resolve_values = $world->getGameStateJson(GAMESTATE_JSON_RESOLVE_VALUE_STACK);
  //   $resolve_values = array_filter($resolve_values, function ($resolve_value) {
  //     // XXX: At the moment, this will remove *all* target-selection values.
  //     //
  //     // How can we be more selective?  Perhaps we remove the
  //     // effect first, and then only remove target-selection
  //     // values with higher productionDepth?
  //     return $resolve_value['sourceType'] != 'TARGET_SELECTION';
  //   });
  //   $world->setGameStateJson(GAMESTATE_JSON_RESOLVE_VALUE_STACK, $resolve_values);

  //   // Remove the canceled effect from the resolve-stack.
  //   //
  //   // XXX: Some more error-checking would be great here.  Include
  //   // something like stack-depth?
  //   $effect = $world->popFromResolveStack();
  //   if ($effect['effectType'] != 'use-gear-card') {
  //     // XXX: This is not actually the only effect that might
  //     // eventually use target selection; e.g. event cards will
  //     // need it as well.
  //     throw new \BgaVisibleSystemException(
  //       'Internal error: unexpected effect on top of resolve stack while canceling target selection.'
  //     );
  //   }

  //   $this->nextState('tResolveEffects');
  // }
}
