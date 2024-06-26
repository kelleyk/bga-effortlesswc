<?php declare(strict_types=1);

echo '*** XXX: Loading localarena_config.inc.php for effortlesswc...' . "\n";

// require_once '/src/localarena/vendor/opis/';

// This file is included by LocalArena; it allows for behavior specific to LocalArena, such as interface schema
// validation, to be configured.  A special $localarenaGameConfig object is available in this context.

// N.B.: This is generated during the build process; it defines a constant named CLIENT_INTERFACE_SCHEMA that contains
// JSON Schema for all notifs, args, etc. that the server sends to the client.
require_once 'modules/php/client_interface_schema.inc.php';

use Opis\JsonSchema\{
  Validator,
  ValidationResult,
  Helper,
};

$validator = new Validator();

$schemas = [];
$combined_schema = json_decode(CLIENT_INTERFACE_SCHEMA, /*associative=*/true);
foreach ($combined_schema['definitions'] as $schema_name => $schema) {
  $schemas[$schema_name] = $validator->loader()->loadObjectSchema((object)$schema);
}

function validateJsonSchema($schema_name, $data) {
  if (!array_key_exists($schema_name, $schemas)) {
    throw new \BgaVisibleSystemException('Client-interface schema validation failed: schema not found: ' . $schema_name);
  }
  $schema = $schemas[$schema_name];

  $result = $validator->validate($data, $schema);

  // XXX: Eventually, we should improve this to show all errors (`getErrors()`) and not just the first one.
  $err = $result->getFirstError();
  if ($err !== null) {
    throw $err;
  }
}

// Called to validate the return value of `getAllDatas()`.
$localarenaGameConfig->registerAllDatasValidator(function ($all_datas) {
  validateJsonSchema('AllDatas', $data);
});

// Called to validate the return value of each state args function.
$localarenaGameConfig->registerArgsValidator(function ($state_name, $data) {
  validateJsonSchema(ucfirst($state_name) . 'Args', $data);
});

// Called to validate the contents of each notif.
$localarenaGameConfig->registerNotifValidator(function ($notif_name, $data) {
  validateJsonSchema(ucfirst($notif_name) . 'Notif', $data);
});

echo '*** XXX: Done loading localarena_config.inc.php for effortlesswc...' . "\n";
