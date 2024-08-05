<?php declare(strict_types=1);

namespace Effortless\Util;

use Effortless\Models\Card;
use Effortless\Models\Location;
use Effortless\Models\Seat;
use Effortless\World;
use Effortless\Models\EffortPile;

// const INPUTTYPE_LOCATION = 'inputtype:location';
// const INPUTTYPE_CARD = 'inputtype:card';
// const INPUTTYPE_EFFORT_PILE = 'inputtype:effort-pile';
//
// XXX: Deal with namespacing.

define('INPUTTYPE_LOCATION', 'inputtype:location');
define('INPUTTYPE_CARD', 'inputtype:card');
define('INPUTTYPE_EFFORT_PILE', 'inputtype:effort-pile');

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
  public string $input_type;
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
      $this->input_type = $json['inputType'];
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
      'inputType' => $this->input_type,
      'choices' => $this->choices,
    ];
  }

  public function renderForClient(World $world)
  {
    return [
      'description' => $this->description,
      'descriptionmyturn' => $this->descriptionmyturn,
      'cancellable' => $this->cancellable,
      'choices' => $this->choices,
      'inputType' => $this->input_type,
    ];
  }
}

// XXX: Why is this mixed into Location again?
trait ParameterInput
{
  // private ?int $next_read_index_ = null;

  // // XXX: We're going to try this without $param_index this time.
  // private int $next_param_index_ = 0;

  // // We want to reset the $next_param_index_ counter on each state transition; we can tell if a state transition has
  // // happened because the $next_move_id_ (BGA's global #6) will have changed.  This stores the last value we saw.
  // private int $next_move_id_ = -1;

  // We consume value-stack entries as we go, but imagine that we consume a first input and then run into the need for a
  // second.  When that happens, we need to put the value stack back the way it was so that the first input is still
  // there when we get back from accepting the second.
  private $initial_value_stack_ = null;

  private $initial_read_index_ = null;

  private function wc_debug(World $world, string $msg): void
  {
    $world->table()->debug('[PARAMINPUT] ' . $msg);
    $world->table()->debug($this->paramInput_dumpState($world));
  }

  // This function is called on every state transition.
  public function paramInput_onEnteringState(World $world, int $state_id): void
  {
    if ($state_id !== ST_INPUT) {
      // // XXX: This is part of our hack to make the parameter-input system work.  Setting the readbase index to the next
      // // index is important; this is how we know which indices will be assigned to input requested by this new state.
      // $next_param_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX);
      // $world->table()->setGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX, $next_param_index);
      $next_write_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX);
      $next_read_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX);
      // if ($next_read_index !== $next_write_index) {
      //   throw new \WcLib\Exception(
      //     'At state transition, read and write indices do not match.  (next_read=' .
      //       $next_read_index .
      //       ' next_write=' .
      //       $next_write_index .
      //       ')'
      //   );
      // }

      $this->wc_debug($world, 'paramInput_onEnteringState($state_id=' . $state_id . ')');
      $this->paramInput_setRevertPoint($world);
    }
  }

  public function paramInput_dumpState(World $world): string
  {
    $read_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX);
    $write_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX);

    $paraminput_config = $world->table()->getParamInputConfig();
    $ici = $paraminput_config->param_index ?? 'null';
    return '[PARAMINPUT] input-config-index=' .
      $ici .
      ' next-read-index=' .
      $read_index .
      ' next-write-index=' .
      $write_index .
      ' value-stack=' .
      print_r($world->table()->valueStack->getValueStack(), true);
  }

  private function paramInput_setRevertPoint(World $world)
  {
    $this->initial_value_stack_ = $world->table()->valueStack->getValueStack();
    $this->initial_read_index_ = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX);
    $this->wc_debug($world, 'paramInput_setRevertPoint(); next_read_index=' . $this->initial_read_index_);
  }

  // XXX: The `getParameter()` stuff should be moved to their own trait; they throw a special exception if we need to
  // ask for user input still.
  public function getParameterEffortPile(World $world, $valid_targets, $args = null): EffortPile
  {
    $this->wc_debug($world, 'getParameterEffortPile()');

    if ($args === null) {
      $args = [];
    }

    // XXX: I'm not sure how I feel about having these defaults.
    if (!array_key_exists('description', $args)) {
      $args['description'] = '${actplayer} must pick an effort pile.';
    }
    if (!array_key_exists('descriptionmyturn', $args)) {
      $args['descriptionmyturn'] = '${you} must pick an effort pile.';
    }

    $json_choices = array_values(
      array_map(function ($pile) use ($world) {
        return $pile->id();
      }, $valid_targets)
    );

    $value_stack_entry = $this->getParameterInner($world, INPUTTYPE_EFFORT_PILE, $json_choices, $args);

    $raw_value = $value_stack_entry['value'];
    return EffortPile::mustGetById($world, $raw_value);
  }

  /**
    @param Location[] $valid_targets
   */
  public function getParameterLocation(World $world, $valid_targets, $args = null): Location
  {
    $this->wc_debug($world, 'getParameterLocation()');

    if ($args === null) {
      $args = [];
    }

    // XXX: I'm not sure how I feel about having these defaults.
    if (!array_key_exists('description', $args)) {
      $args['description'] = '${actplayer} must pick a location.';
    }
    if (!array_key_exists('descriptionmyturn', $args)) {
      $args['descriptionmyturn'] = '${you} must pick a location.';
    }

    $json_choices = array_values(
      array_map(function ($location) {
        return $location->id();
      }, $valid_targets)
    );

    $value_stack_entry = $this->getParameterInner($world, INPUTTYPE_LOCATION, $json_choices, $args);
    // if ($value_stack_entry === null) {
    //   return null;
    // }
    $raw_value = $value_stack_entry['value'];
    return Location::mustGetById($world, $raw_value);
  }

  public function getParameterCardInHand(World $world, Seat $seat, $args = null): Card
  {
    $this->wc_debug($world, 'getParameterCardInHand()');

    $args = $args ?? [];
    $args['selectionType'] = 'fromPrompt';

    return $this->getParameterCard($world, $seat->hand($world), $args);
  }

  // XXX: This needs to show the player making the decision any face-down cards when they make their decision.
  public function getParameterCardAtLocation(World $world, Location $location, $args = null): Card
  {
    $this->wc_debug($world, 'getParameterCardAtLocation()');

    // XXX: for now, at least, using 'fromPrompt' rather than 'inPlay' is just a way to avoid the "reveal face-down
    // cards" problem
    $args = $args ?? [];
    $args['selectionType'] = 'fromPrompt';

    return $this->getParameterCard($world, $location->cards($world), $args);
  }

  // There are two allowed values for $args['selectionType']:
  //
  // - "inPlay": This is one of the "foundational" card input mechanisms.  It allows the player to select one of the
  //   given cards, which must be "in play" (at a location).
  //
  // - "fromPrompt": This is one of the "foundational" card input mechanisms.  It shows the player all of the given
  //   cards in a prompt and lets them choose one.  The cards may *also* be in play, but they are not required to be.
  //
  // XXX: We need to send full data about the $cards to the client even if they are face-down.
  //
  public function getParameterCard(World $world, $cards, $args = null): Card
  {
    $this->wc_debug($world, 'getParameterCard()');

    if ($args === null) {
      $args = [];
    }

    // XXX: I'm not sure how I feel about having these defaults.
    if (!array_key_exists('description', $args)) {
      $args['description'] = '${actplayer} must pick a card.';
    }
    if (!array_key_exists('descriptionmyturn', $args)) {
      $args['descriptionmyturn'] = '${you} must pick a card.';
    }

    $json_choices = array_values(
      array_map(function ($card) use ($world) {
        // N.B.: We have to send all of the metadata about the card, instead of only its ID, because it might not
        // necessarily be face-up on the board.  That's what the $force_visible parameter here does.
        return $card->renderForClient($world, /*force_visible=*/ true);
      }, $cards)
    );

    $value_stack_entry = $this->getParameterInner($world, INPUTTYPE_CARD, $json_choices, $args);
    // if ($value_stack_entry === null) {
    //   return null;
    // }
    $raw_value = $value_stack_entry['value'];
    // throw new \feException('XXX: raw_value=' . $raw_value);
    return $world->table()->mainDeck->mustGet($raw_value);
  }

  // // Each element of $valid_targets is a `Card`.
  // public function getParameterCard(World $world, $valid_targets, $args = null)
  // {
  //   throw new \feException('XXX: no impl: getParameterCard');
  // }

  private function getNextReadIndex(World $world): int
  {
    $this->wc_debug($world, 'getNextReadIndex()');

    return $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX);
    // if ($this->next_read_index_ === null) {
    //   $this->next_read_index_ = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX);
    // }
    // return $this->next_read_index_;
  }

  private function consumeNextReadIndex(World $world): int
  {
    $this->wc_debug($world, 'consumeNextReadIndex()');

    $read_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX);
    $world->table()->setGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX, $read_index + 1);
    $this->wc_debug($world, 'PARAMINPUT: consumeNextReadIndex(): ' . $read_index);
    return $read_index;
  }

  private function getNextParameterIndex(World $world): int
  {
    $this->wc_debug($world, 'getNextParameterIndex()');

    return $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX);
  }

  private function consumeNextParameterIndex(World $world): int
  {
    $this->wc_debug($world, 'consumeNextParameterIndex()');

    $next_param_index = $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX);
    $world->table()->setGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX, $next_param_index + 1);
    $this->wc_debug($world, 'PARAMINPUT: consumeNextParameterIndex(): ' . $next_param_index);
    return $next_param_index;
  }

  // private function getNextMoveId(World $world): int
  // {
  //   // XXX: This could be implemented with the following line, except that the caching that the BGA globals system does
  //   // will break our ability to detect when we've transitioned to the next state.
  //   //
  //   // intval($world->table()->getGameStateValue('next_move_id'));

  //   // XXX: This doesn't seem to be changed during one server-side call, even when we move through multiple states.
  //   return intval($world->table()->getUniqueValueFromDB('SELECT global_value FROM global WHERE global_id = 3;'));
  // }

  // private function consumeNextParameterIndex(World $world): int
  // {
  //   $next_move_id = $this->getNextMoveId($world);
  //   // // XXX:
  //   // $next_move_id = -1;

  //   if ($this->next_move_id_ != $next_move_id) {
  //     $this->next_move_id_ = $next_move_id;
  //     $this->next_param_index_ = 0;
  //     $this->initial_value_stack_ = $world->table()->valueStack->getValueStack();
  //   }

  //   $retval = $this->next_param_index_++;
  //   if ($retval > 0) {
  //     throw new \WcLib\Exception('param index: ' . $retval . '; next_move_id=' . $next_move_id);
  //   }
  //   return $retval;
  // }

  private function revertValueStack(World $world): void
  {
    $this->wc_debug($world, 'revertValueStack(): setting readbase index back to ' . $this->initial_read_index_);

    // If we haven't initialized $this->initial_value_stack_, then we're reverting before ever modifying the stack.
    if ($this->initial_value_stack_ !== null) {
      $world->table()->valueStack->setValueStack($this->initial_value_stack_);
      // XXX: The -1 here is a hack; I'm not sure that it will work in multiple-input situations, and I don't (yet)
      // understand why it's necessary.
      $world->table()->setGameStateInt(GAMESTATE_INT_PARAMINPUT_READBASE_INDEX, $this->initial_read_index_ - 1);
    }
  }

  // XXX: we want the config to be passed in from the call site
  //
  // - except $
  private function getParameterInner(World $world, string $input_type, $json_choices, $args)
  {
    $this->wc_debug($world, 'getParameterInner()');

    $default_return_transition = 'ret:' . $world->table()->gamestate->state()['name'];

    $args['cancellable'] = $args['cancellable'] ?? false;
    $args['stateArgs'] = $args['stateArgs'] ?? [];
    $args['returnTransition'] = $args['returnTransition'] ?? $default_return_transition;

    if (count($json_choices) == 0) {
      // XXX: What if this happens as the result of an uncancellable effect?
      throw new NoChoicesAvailableException('There are no valid targets for this effect!');
    }

    // throw new \WcLib\Exception(
    //   'next_index=' .
    //     $world->table()->getGameStateInt(GAMESTATE_INT_PARAMINPUT_NEXT_INDEX) .
    //     ' next_read_index=' .
    //     $this->getNextReadIndex($world)
    // );

    $read_index = $this->consumeNextReadIndex($world);

    // We need to save the initial value of the stack in case we wind up needing to revert (because e.g. we need
    // additional input before we can finish resolving this state).  Traits can't have constructors, so we lazily
    // initialize this before the first time we modify the value-stack.
    if ($this->initial_value_stack_ === null) {
      $this->wc_debug($world, 'getParameterInner() -- initializing initial_value_stack_');
      $this->paramInput_setRevertPoint($world);
    }

    // First, let's check and see if we already have a value waiting.
    $resolve_value = $world->table()->valueStack->consumeFirstMatching(function ($resolve_value) use ($read_index) {
      return $resolve_value['sourceType'] == 'USER_INPUT' && $resolve_value['paramIndex'] == $read_index;
    });

    // $value_stack = $world->table()->valueStack->getValueStack();
    // throw new \BgaVisibleSystemException('XXX: Internal error: value-stack contents: ' . print_r($value_stack, true));

    // // // XXX: Add a "require to be unique" flag?
    // if (count($resolve_values) > 1) {
    //   throw new \BgaVisibleSystemException('Internal error: more than one resolve-value matched filter criteria.');
    // }

    // XXX: We need to remove the consumed value from the resolve-value stack, or else it'll get seen the next time
    // someone asks, too.

    // echo '*** resolve_values for $param_index=' . $param_index . ': ' . print_r($resolve_values, true) . "\n----\n";

    if ($resolve_value !== null) {
      $this->wc_debug($world, 'PARAMINPUT: we have a value for read_index=' . $read_index);
      // throw new \feException('hey hey we have a value for you!');

      // $world
      //   ->table()
      //   ->notifyAllPlayers(
      //     'XXX_debug',
      //     'we have a resolve-value for param ' . $param_index . ': ' . print_r($resolve_value, true),
      //     []
      //   );

      // Okay, we have a value; let's return it!  User-input
      // validation has already happened (when ST_TARGET_SELECTION
      // accepted that input); we could repeat it here for
      // defense-in-depth if we wanted.

      if ($resolve_value['valueType'] != $input_type) {
        throw new \BgaVisibleSystemException('Internal error: unexpected type for resolve-value.');
      }
      return $resolve_value;
    }
    $this->wc_debug(
      $world,
      'PARAMINPUT: we DO NOT have a value for read_index=' . $read_index . '; moving to input state'
    );

    // XXX: We're going to need to add the code that generates the target index automatically!
    //
    //   - XXX: ... hmm, how will it reset when we transition between states?  Can we use something like the move
    //     number?

    $paraminput_config = new ParameterInputConfig(
      array_merge($args, [
        'inputType' => $input_type,
        'choices' => $json_choices,
        'paramIndex' => $this->consumeNextParameterIndex($world),
      ])
    );

    // if ($param_index > 0) {
    // throw new \WcLib\Exception('oops: about to push a paraminput-config: ' . print_r($paraminput_config, true));
    // }

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

    // throw new \feException('XXX: input needed: ' . print_r(json_encode($paraminput_config), true));
    $world->nextState(T_GET_INPUT);
    $this->revertValueStack($world);
    throw new InputRequiredException();
  }
}
