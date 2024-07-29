<?php declare(strict_types=1);

set_include_path(
  implode(PATH_SEPARATOR, [
    get_include_path(),
    '/src/game/effortless',
    '/src/test/effortless',
  ])
);

// XXX: This should be provided from LocalArena; we should investigate why it isn't.
function totranslate($x) {
  return $x;
}

$opts = getopt('', ['metadata-type:', 'output:', 'input:']);

switch ($opts['metadata-type']) {
case 'gameoptions': {
  $game_options = [];
  require_once($opts['input']);
  $json_str = json_encode($game_options, JSON_PRETTY_PRINT);
  file_put_contents($opts['output'], $json_str);
  break;
}
case 'gamepreferences': {
  $game_preferences = [];
  require_once($opts['input']);
  $json_str = json_encode($game_preferences, JSON_PRETTY_PRINT);
  file_put_contents($opts['output'], $json_str);
  break;
}
default: {
  fwrite(STDERR, "Unrecognized `--metadata-type`.");
  exit(1);
}
}
