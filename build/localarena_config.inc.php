<?php declare(strict_types=1);

echo '*** XXX: Loading localarena_config.inc.php for effortlesswc...' . "\n";

// This file is included by LocalArena; it allows for behavior specific to LocalArena, such as interface schema
// validation, to be configured.  A special $localarenaGameConfig object is available in this context.

// N.B.: This is generated during the build process; it defines a constant named CLIENT_INTERFACE_SCHEMA that contains
// JSON Schema for all notifs, args, etc. that the server sends to the client.
require_once 'modules/php/client_interface_schema.inc.php';

use Opis\JsonSchema\{Validator, ValidationResult, Helper};

use Opis\JsonSchema\Errors\{ErrorFormatter, ValidationError};

global $validator;
$validator = new Validator();

$combined_schema = json_decode(CLIENT_INTERFACE_SCHEMA, /*associative=*/ false);
$validator->resolver()->registerRaw((object) $combined_schema, 'http://localarena.example.com/schema.json');

function objectize($x)
{
  switch (gettype($x)) {
    case 'array':
      return (object) array_map(function ($y) {
        return objectize($y);
      }, $x);
    default:
      return $x;
  }
}

function validateJsonSchema($schema_name, $data, $msg)
{
  global $validator;

  // N.B.: Unlike some of the other validation methods, `uriValidation()` returns a single ValidationError or null.
  $err = $validator->uriValidation(
    objectize($data),
    'http://localarena.example.com/schema.json' . '#/definitions/' . $schema_name
  );
  if ($err !== null) {
    $formatter = new ErrorFormatter();
    throw new \Exception(
      'Failed to validate ' .
        $msg .
        ' against schema `' .
        $schema_name .
        '`: ' .
        implode("\n\n--------------\n\n", $formatter->format($err, false))
    );
  }
}

function localarenaConfigureGame($localarenaGameConfig)
{
  // Called to validate the return value of `getAllDatas()`.
  $localarenaGameConfig->registerAllDatasValidator(function ($all_datas) {
    validateJsonSchema('Gamedatas', $all_datas, 'gamedatas');
  });

  // Called to validate the return value of each state args function.
  $localarenaGameConfig->registerArgsValidator(function ($state_name, $data) {
    validateJsonSchema(ucfirst($state_name) . 'Args', $data, 'args for state "' . $state_name . '"');
  });

  // Called to validate the contents of each notif.
  $localarenaGameConfig->registerNotifValidator(function ($notif_name, $data) {
    validateJsonSchema(ucfirst($state_name) . 'Notif', $data, 'data for notif "' . $notif_name . '"');
  });

  echo '*** XXX: done with `localarenaConfigureGame()...' . "\n";
}

echo '*** XXX: Done loading localarena_config.inc.php for effortlesswc...' . "\n";
