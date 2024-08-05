<?php declare(strict_types=1);

namespace WcLib;

// This is based on the "resolve-value stack" mechanism that I wrote for Burgle Bros 2.  It's used by the
// parameter-input system as well.
//
// It depends on \WcLib\GameState for persistence.
//
// It could almost certainly be improved quite a bit; and there's a lot more stuff that we could move into this library
// and out of the individual games.

class ValueStack {
  // This needs to be a GAMESTATE_JSON_* value.
  protected string $gamestate_key_;

  // This is something that uses the `GameState` trait, typically the game-wide table class.
  /** @var GameState */
  protected $gamestate_impl_;

  /**
    @param GameState $gamestate_impl
  */
  function __construct($gamestate_impl, string $gamestate_key) {
    $this->gamestate_impl_ = $gamestate_impl;
    $this->gamestate_key_ = $gamestate_key;
  }

  /**
    @param mixed[] $value
  */
  function push($value): void
  {
    if (!is_array($value)) {
      throw new \BgaVisibleSystemException(
        'Internal error: each element pushed onto the resolve-value stack must be an array describing a resolve-value.'
      );
    }

    // echo '*** pushOnResolveValueStack(): ' . print_r($value, true) . "\n---\n";
    // ob_flush();

    // XXX: Validate 'valueType', 'productionDepth', etc.

    $resolve_value_stack = $this->getValueStack();
    $resolve_value_stack[] = $value;
    $this->setValueStack( $resolve_value_stack);
  }

  /**
    @return mixed[]|null
  */
  public function peek()
  {
    $resolve_value_stack = $this->getValueStack();
    if (count($resolve_value_stack) > 0) {
      return $resolve_value_stack[0];
    }
    return null;
  }

  /**
    @return mixed[]|null
  */
  public function pop()
  {
    $resolve_value_stack = $this->getValueStack();
    if (count($resolve_value_stack) > 0) {
      $entry = array_shift($resolve_value_stack);
      $this->setValueStack( $resolve_value_stack);
      return $entry;
    }
    return null;
  }

  /**
    Searches the resolve stack top to bottom (most recently pushed to last) and invokes $predicate on each value entry.
    Returns the first entry for which $predicate returns true, or null iff $predicate does not return true for any
    entry.

    @return mixed[]|null
  */
  public function consumeFirstMatching($predicate) {
    $resolve_value_stack = $this->getValueStack();

    for ($i = count($resolve_value_stack) - 1; $i >= 0; --$i) {
      $entry = $resolve_value_stack[$i];
      if ($predicate($entry)) {
        array_splice($resolve_value_stack, $i, 1);
        $this->setValueStack($resolve_value_stack);
        return $entry;
      }
    }

    return null;
  }

  /**
    @return mixed[][]
  */
  public /*protected*/ function getValueStack() {
    $raw_value = $this->gamestate_impl_->getGameStateJson($this->gamestate_key_);
    if ($raw_value === null) {
      throw new \feException('The game-state value (with key='.$this->gamestate_key_.' backing this value stack is `null`.  It may not have been initialized correctly.');
    }
    return $raw_value;
  }

  /**
    @param mixed[][] $value_stack
  */
  public /*protected*/ function setValueStack($value_stack) {
    return $this->gamestate_impl_->setGameStateJson($this->gamestate_key_, $value_stack);
  }

}
