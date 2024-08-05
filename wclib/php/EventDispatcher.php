<?php declare(strict_types=1);

namespace WcLib;

/**
 * A type-safe single-event dispatcher with variable parameters.
 *
 * @template TParams
 */
class EventDispatcher
{
    /** @var array<callable(TParams): void> */
    private array $listeners = [];

  /**
    @param class-string<TParams> $TParams;
   */
  function __construct($TParams) {
  }

    /**
     * Register a callback for the event.
     *
     * @param callable(TParams): void $callback The callback to be executed when the event is triggered.
     * @return void
     */
    public function addListener(callable $callback): void
    {
        $this->listeners[] = $callback;
    }

    /**
     * Trigger the event, executing all registered callbacks.
     *
     * @param TParams $params
     * @return void
     */
    public function dispatch(mixed ...$params): void
    {
        foreach ($this->listeners as $callback) {
          /** @phan-suppress-next-line PhanParamTooFewUnpack */
            $callback(...$params);
        }
    }

  /**
   * Remove a specific listener from the event.
   *
   * @param callable(TParams): void $callback The callback to be removed.
   * @return bool True if the callback was found and removed, false otherwise.
   */
  public function removeListener(callable $callback): bool
  {
    $key = array_search($callback, $this->listeners, true);
    if ($key !== false) {
      unset($this->listeners[$key]);
      $this->listeners = array_values($this->listeners); // Re-index the array
      return true;
    }
    return false;
  }

    /**
     * Remove all listeners for the event.
     *
     * @return void
     */
    public function removeListeners(): void
    {
        $this->listeners = [];
    }

    /**
     * Get the number of listeners for the event.
     *
     * @return int The number of listeners.
     */
    public function getListenerCount(): int
    {
        return count($this->listeners);
    }
}

/**
 * RAII-style helper class for automatic listener registration and unregistration.
 *
 * @template TParams
 */
class ScopedListener
{
  private EventDispatcher $dispatcher;
  /** @var callable */
  private $callback;

  /**
   * @param EventDispatcher<TParams> $dispatcher The event dispatcher.
   * @param callable(TParams): void $callback The listener callback.
   */
  public function __construct(
    EventDispatcher $dispatcher,
    callable $callback
  ) {
    $this->dispatcher = $dispatcher;
    $this->callback = $callback;

    $this->dispatcher->addListener($this->callback);
  }

  /**
   * Unregister the listener when the object is destroyed.
   */
  public function __destruct()
  {
    $this->dispatcher->removeListener($this->callback);
  }
}
