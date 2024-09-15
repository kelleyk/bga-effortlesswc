<?php declare(strict_types=1);

set_include_path(
  implode(PATH_SEPARATOR, [
    get_include_path(),
    '/src/game/effortless',
    '/src/test/effortless',
    // XXX: removed '/src' as part of path restructuring

    // XXX: Has that broken access to PHPUnit (or even to LocalArena?)
    // BurgleBros adds '/src' to the include path
  ])
);

// // define("APP_GAMEMODULE_PATH", getenv('APP_GAMEMODULE_PATH'));
// const APP_GAMEMODULE_PATH='/src';

// spl_autoload_register(function ($class_name) {
//     switch ($class_name) {
//     case "APP_GameClass":
//         //var_dump($class_name);
//         //var_dump(APP_GAMEMODULE_PATH);
//         include APP_GAMEMODULE_PATH."/module/table/table.game.php";
//         break;
//     default:
//         include $class_name . ".php";
//         break;
//     }
// });

?>
