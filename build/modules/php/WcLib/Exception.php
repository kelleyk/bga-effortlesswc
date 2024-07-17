<?php declare(strict_types=1);

namespace WcLib;

class Exception extends \BgaVisibleSystemException {
  public function __construct($message = '', $code = 0, ?\Exception $previous = null)
  {
    // N.B.: There's more discussion about options for getting a backtrace string here:
    //
    // - https://stackoverflow.com/questions/19644447/debug-print-backtrace-to-string-for-log-file
    //
    $msg =       '[WCLIB-EXC] ' . $message . '<br /><br />' . "\n\n" . implode('<br />'."\n", array_map(
      function ($frame) {
        $frame_class = $frame['class'] ?? '';
        $frame_type = $frame['type'] ?? '';
        return "{$frame['file']}:{$frame['line']} {$frame_class}{$frame_type}{$frame['function']}()";
      },
      debug_backtrace()
    ));

    parent::__construct(
      $msg,
      $code,
      $previous,
    );
  }
}
