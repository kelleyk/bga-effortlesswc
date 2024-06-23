<?php declare(strict_types=1);

/**
  XXX: In order to remove these directives, we need to make LocalArena code visible to Phan. Doing that will require
  dealing with the redefinition conflicts between LocalArena and the stubs in WcLib.

  @XXX-phan-file-suppress PhanUndeclaredConstant
  @XXX-phan-file-suppress PhanUndeclaredExtendedClass
  @XXX-phan-file-suppress PhanUndeclaredClassMethod
  @XXX-phan-file-suppress PhanUndeclaredClassProperty
  @XXX-phan-file-suppress PhanUndeclaredMethod
 */

namespace WcLib\Test;

require_once '/src/localarena/module/test/IntegrationTestCase.php';
require_once '/src/localarena/module/tablemanager/tablemanager.php';

// use LocalArena\Test\PlayerPeer;

// use WcLib\WcDeck;

// use EffortlessWC\Models\Npc;
// use EffortlessWC\Models\Position;

// function array_value_first($arr)
// {
//   $k = array_key_first($arr);
//   return $arr[$k];
// }

class IntegrationTestCase extends \LocalArena\Test\IntegrationTestCase
{
}
