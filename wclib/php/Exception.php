<?php declare(strict_types=1);

namespace WcLib;

class Exception extends \BgaVisibleSystemException {
  public function __toString() {
    // N.B.: There's more discussion about options for getting a backtrace string here:
    //
    // - https://stackoverflow.com/questions/19644447/debug-print-backtrace-to-string-for-log-file
    //
    return '[WCLIB-EXC] ' . parent::__toString() . "\n\n" . print_r(debug_backtrace(), true);
  }
}
