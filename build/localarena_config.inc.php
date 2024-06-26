<?php declare(strict_types=1);

echo '*** XXX: Loading localarena_config.inc.php for effortlesswc...' . "\n";

// This file is included by LocalArena; it allows for behavior specific to LocalArena, such as interface schema
// validation, to be configured.  A special $localarenaGameConfig object is available in this context.

// N.B.: This is generated during the build process; it defines a constant named CLIENT_INTERFACE_SCHEMA that contains
// JSON Schema for all notifs, args, etc. that the server sends to the client.
require_once 'modules/php/client_interface_schema.inc.php';

use \Opis\JsonSchema\{Validator, ValidationResult, Helper};

use Opis\JsonSchema\Errors\{
ErrorFormatter,
  ValidationError,
  };

global $validator;
$validator = new Validator();

// global $schemas;
// $schemas = [];
global $combined_schema;
$combined_schema = json_decode(CLIENT_INTERFACE_SCHEMA, /*associative=*/ false);
// foreach ($combined_schema['definitions'] as $schema_name => $schema) {
//   echo '*** ZZZ: about to parse...' . "\n";
//   $schemas[$schema_name] = $validator->loader()->loadObjectSchema((object) $schema);
//   echo '*** ZZZ: just parsed...' . "\n";
// }

// $validator->loader()->loadObjectSchema((object) $combined_schema);

$validator->resolver()->registerRaw((object)$combined_schema, 'http://localarena.example.com/schema.json');

function validateJsonSchema($schema_name, $data)
{
  global $validator;
  // global $schemas;
  global $combined_schema;

  // if (!array_key_exists($schema_name, $schemas)) {
  //   throw new \BgaVisibleSystemException(
  //     'Client-interface schema validation failed: schema not found: ' . $schema_name
  //   );
  // }
  // $schema = $schemas[$schema_name];
  // $result = $validator->validate($data, $schema);

  // XXX: Unlike some of the other validation methods, `uriValidation()` returns a single ValidationError or null.
  $err = $validator->uriValidation((object)$data, 'http://localarena.example.com/schema.json' . '#/definitions/' . $schema_name);
  if ($err !== null) {

    // throw new \Exception(get_class($err));

    $formatter = new ErrorFormatter();
    throw new \Exception(
      'Schema validation error: ' .
        implode("\n\n--------------\n\n", $formatter->format($err, false)));

    // throw new \Exception('Schema validation error on keyword "'.$result->keyword().'": ' . $result->message() . "\n\n" . 'Schema ' . print_r($result->schema(), true) . ' and data '
    //                      // . "\n" . print_r($result->data(), true)
  }

  // // XXX: Eventually, we should improve this to show all errors (`getErrors()`) and not just the first one.
  // $err = $result->getFirstError();
  // if ($err !== null) {
  //   throw $err;
  // }
}

function localarenaConfigureGame($localarenaGameConfig) {
  // Called to validate the return value of `getAllDatas()`.
  $localarenaGameConfig->registerAllDatasValidator(function ($all_datas) {
    validateJsonSchema('Gamedatas', $all_datas);
  });

  // Called to validate the return value of each state args function.
  $localarenaGameConfig->registerArgsValidator(function ($state_name, $data) {
    validateJsonSchema(ucfirst($state_name) . 'Args', $data);
  });

  // Called to validate the contents of each notif.
  $localarenaGameConfig->registerNotifValidator(function ($notif_name, $data) {
    validateJsonSchema(ucfirst($notif_name) . 'Notif', $data);
  });

  echo '*** XXX: done with `localarenaConfigureGame()...' . "\n";
}

echo '*** XXX: Done loading localarena_config.inc.php for effortlesswc...' . "\n";
