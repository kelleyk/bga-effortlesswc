<?php declare(strict_types=1);

namespace WcLib;

trait Logging
{
  use \WcLib\BgaTableTrait;

  // XXX: Can we make this static somehow?  Can we add structured
  //   logging (and perhaps, in LocalArena, pipe that to another
  //   container that can let us explore it?)
  public function wc_trace(string $msg): void
  {
    if (php_sapi_name() == 'cli') {
    // XXX: use something like
    //   debug_backtrace()[1]['function'];
    // ... to selectively change output levels?
    $state_name = $this->gamestate->state()['name'];
    echo '[TRACE] [' . $state_name . '] ' . $msg . "\n";
    }
  }

  // function notifyDebug($scope, $msg): void
  // {
  //   // // XXX: make this more configurable
  //   // if ($scope == "ResolveEffect") {
  //   //     return;
  //   // }
  //
  //   if (php_sapi_name() == 'cli') {
  //     // XXX: This should be "if running under PHPUnit tests..."
  //     echo $scope . ': ' . $msg . "\n";
  //   }
  //
  //   self::notifyAllPlayers('debugMessage', $scope . ': ' . $msg, []);
  // }
}
