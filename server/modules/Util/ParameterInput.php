<?php declare(strict_types=1);

// TODO: Eventually, at least the core of this should be extracted to WcLib for reuse.

namespace EffortlessWC\Util;

use EffortlessWC\Models\Card;
use EffortlessWC\Models\Location;
use EffortlessWC\Models\Seat;
use EffortlessWC\World;

const INPUTTYPE_LOCATION = 'inputtype:location';
const INPUTTYPE_CARD = 'inputtype:card';
const INPUTTYPE_EFFORT_PILE = 'inputtype:effort-pile';

abstract class ParameterInputException extends \Exception
{
}

class InputCancelledException extends ParameterInputException
{
}

class InputRequiredException extends ParameterInputException
{
}

class NoChoicesAvailableException extends ParameterInputException
{
}

class ParameterInputConfig implements \JsonSerializable
{
  // These params are accepted in the $args param.
  public bool $cancellable;
  public string $description;
  public string $descriptionmyturn;
  /** @var mixed[] */
  public $state_args;

  // These parameters are set automatically.
  public string $return_transition;
  public int $param_index;

  // These parameters are set based on non-$args params.
  public string $expected_type;
  public $choices;

  public function __construct($json = null)
  {
    if ($json !== null) {
      $this->cancellable = boolval($json['cancellable']);
      $this->description = $json['description'];
      $this->descriptionmyturn = $json['descriptionmyturn'];
      $this->state_args = $json['stateArgs'];
      $this->return_transition = $json['returnTransition'];
      $this->param_index = $json['paramIndex'];
      $this->expected_type = $json['expectedType'];
      $this->choices = $json['choices'];
    }
  }

  public function jsonSerialize(): mixed
  {
    return [
      'cancellable' => $this->cancellable,
      'description' => $this->description,
      'descriptionmyturn' => $this->descriptionmyturn,
      'stateArgs' => $this->state_args,
      'returnTransition' => $this->return_transition,
      'paramIndex' => $this->param_index,
      'expectedType' => $this->expected_type,
      'choices' => $this->choices,
    ];
  }
}

trait ParameterInput
{
  // XXX: We're going to try this without $param_index this time.
  private int $next_param_index_ = 0;

  // We want to reset the $next_param_index_ counter on each state transition; we can tell if a state transition has
  // happened because the $next_move_id_ (BGA's global #6) will have changed.  This stores the last value we saw.
  private int $next_move_id_ = -1;

  // XXX: The `getParameter()` stuff should be moved to their own trait; they throw a special exception if we need to
  // ask for user input still.
  public function getParameterEffortPile(World $world, $valid_targets = null)
  {
    throw new \feException('XXX: no impl: getParameterEffortPile');
  }

  /**
    @param Location[] $valid_targets
   */
  public function getParameterLocation(World $world, $valid_targets, $args = null): Location
  {
    if ($args === null) {
      $args = [];
    }

    $json_choices = array_values(
      array_map(function ($location) {
        return $location->id();
      }, $valid_targets)
    );

    $raw_value = $this->getParameterInner($world, INPUTTYPE_LOCATION, $json_choices, $args);
    // if ($raw_value === null) {
    //   return null;
    // }
    return Location::mustGetById($world, $raw_value);
  }

  public function getParameterCardInHand(World $world, Seat $seat, $args = null): Card
  {
    // XXX: This should be a thin layer over `getParameterCard()`.
    throw new \feException('XXX: no impl: getParameterCardInHand');
  }

  // XXX: This needs to show the player making the decision any face-down cards when they make their decision.
  public function getParameterCardAtLocation(World $world, Location $loc, $args = null)
  {
    // XXX: This should be a thin layer over `getParameterCard()`.
    throw new \feException('XXX: no impl: getParameterCardAtLocation');
  }

  // Each element of $valid_targets is a `Card`.
  public function getParameterCard(World $world, $valid_targets, $args = null)
  {
    throw new \feException('XXX: no impl: getParameterCard');
  }

  private function consumeNextParameterIndex(): int
  {
    // $next_move_id = $this->getGameStateValue('next_move_id');
    // XXX:
    $next_move_id = -1;

    if ($this->next_move_id_ != $next_move_id) {
      $this->next_move_id_ = $next_move_id;
      $this->next_param_index_ = 0;
    }

    return $this->next_param_index_++;
  }

  // XXX: we want the config to be passed in from the call site
  //
  // - except $
  private function getParameterInner(World $world, string $expected_type, $json_choices, $args)
  {
    $default_return_transition = 'ret:' . $world->table()->gamestate->state()['name'];

    $args['cancellable'] = $args['cancellable'] ?? false;
    $args['stateArgs'] = $args['stateArgs'] ?? [];
    $args['returnTransition'] = $args['returnTransition'] ?? $default_return_transition;

    if (count($json_choices) == 0) {
      // XXX: What if this happens as the result of an uncancellable effect?
      throw new NoChoicesAvailableException('There are no valid targets for this effect!');
    }

    $param_index = $this->consumeNextParameterIndex();

    // First, let's check and see if we already have a value waiting.
    $resolve_values = $world->table()->getGameStateJson(GAMESTATE_JSON_RESOLVE_VALUE_STACK);
    $resolve_values = array_filter($resolve_values, function ($resolve_value) use ($param_index) {
      return $resolve_value['sourceType'] == 'TARGET_SELECTION' && $resolve_value['targetIdx'] == $param_index;
    });
    if (count($resolve_values) > 1) {
      throw new \BgaVisibleSystemException('Internal error: more than one resolve-value matched filter criteria.');
    }

    // echo '*** resolve_values for $param_index=' . $param_index . ': ' . print_r($resolve_values, true) . "\n----\n";

    if (count($resolve_values) == 1) {
      // Okay, we have a value; let's return it!  User-input
      // validation has already happened (when ST_TARGET_SELECTION
      // accepted that input); we could repeat it here for
      // defense-in-depth if we wanted.
      $resolve_value = $resolve_values[array_key_first($resolve_values)];

      if ($resolve_value['valueType'] != $expected_type) {
        throw new \BgaVisibleSystemException('Internal error: unexpected type for resolve-value.');
      }
      return $resolve_value;
    }

    // XXX: We're going to need to add the code that generates the target index automatically!
    //
    //   - XXX: ... hmm, how will it reset when we transition between states?  Can we use something like the move
    //     number?

    $paraminput_config = new ParameterInputConfig(
      array_merge($args, [
        'expectedType' => $expected_type,
        'choices' => $json_choices,
        'paramIndex' => $param_index,
      ])
    );

    // Okay: we don't have a value waiting, so let's ask the player for input.
    $world->table()->setGameStateJson(GAMESTATE_JSON_PARAMINPUT_CONFIG, $paraminput_config->jsonSerialize());

    // XXX: I'm still not 100% sure if we *always* want to handle the state transition here, rather than in the calling
    // code.
    //
    // XXX: Remember that ST_GET_INPUT is now a multiactive state.  We probably do need to extend these APIs to allow
    // input from player(s) other than "active" one.

    $input_player = $world->activeSeat()->inputPlayer($world);
    $world
      ->table()
      ->gamestate->setPlayersMultiactive([$input_player->id()], $paraminput_config->return_transition, true);

    $world->nextState(T_GET_INPUT);
    throw new InputRequiredException();
  }
}
