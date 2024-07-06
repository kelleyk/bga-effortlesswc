<?php declare(strict_types=1);

namespace WcLib;

class Exception extends \BgaVisibleSystemException {
  public function __construct($message = '', $code = 0, ?\Exception $previous = null)
  {
    // N.B.: There's more discussion about options for getting a backtrace string here:
    //
    // - https://stackoverflow.com/questions/19644447/debug-print-backtrace-to-string-for-log-file
    //
    parent::__construct(
      '[WCLIB-EXC] ' . $message . "\n\n" . print_r(debug_backtrace(), true),
      $code,
      $previous,
    );
  }
}
