<?php declare(strict_types=1);

namespace BurgleBrosTwo\Models;

use BurgleBrosTwo\Interfaces\World;

use BurgleBrosTwo\Managers\Card;

use BurgleBrosTwo\Models\PlayerCharacter;
use BurgleBrosTwo\Models\Position;
use BurgleBrosTwo\Models\Tile;

// Contains logic shared between `GearCard` and `EventCard`.
abstract class CardBase
{
  private function getParameterInner(World $world, int $i, string $expected_type, $json_choices, bool $cancellable)
  {
    if (count($json_choices) == 0) {
      // XXX: What if this happens as the result of an
      // uncancellable effect (such as an event card)?
      throw new \BgaUserException('There are no valid targets for this effect!');
    }

    // First, let's check and see if we already have a value waiting.
    $resolve_values = $world->getGameStateJson(GAMESTATE_JSON_RESOLVE_VALUE_STACK);
    $resolve_values = array_filter($resolve_values, function ($resolve_value) use ($i) {
      return $resolve_value['sourceType'] == 'TARGET_SELECTION' && $resolve_value['targetIdx'] == $i;
    });
    if (count($resolve_values) > 1) {
      throw new \BgaVisibleSystemException('Internal error: more than one resolve-value matched filter criteria.');
    }

    echo '*** resolve_values for $i=' . $i . ': ' . print_r($resolve_values, true) . "\n----\n";

    if (count($resolve_values) == 1) {
      // Okay, we have a value; let's return it!  User-input
      // validation has already happened (when ST_TARGET_SELECTION
      // accepted that input); we could repeat it here for
      // defense-in-depth if we wanted.
      $resolve_value = $resolve_values[array_key_first($resolve_values)];

      if ($resolve_value['valueType'] != $expected_type) {
        throw new \BgaVisibleSystemException('Internal error: unexpected type for resolve-value.');
      }
      return $resolve_value;
    }

    // Okay: we don't have a value waiting, so let's ask the player for input.
    $world->setGameStateJson(GAMESTATE_JSON_TARGET_SELECTION, [
      'valueType' => $expected_type,
      'choices' => $json_choices,
      'targetIdx' => $i,
      'cancellable' => $cancellable,
    ]);
    $world->nextState('tSelectTarget');
    return null;
  }

  // Asks the player to select a value for parameter $i among the
  // given $choices.
  //
  // Iff the player has not yet made a choice, makes the state
  // transitions necessary to ask the player for input and returns
  // null.  Calling code should generally return immediately when this
  // happens.
  //
  // Elements of $choices may be `Tile` or `Position`.
  protected function getTileParameter(World $world, int $i, $choices, bool $cancellable = true): ?Tile
  {
    $json_choices = [];
    foreach ($choices as $choice) {
      if ($choice instanceof Tile) {
        $json_choices[] = $choice->pos->toArray();
      } elseif ($choice instanceof Position) {
        $json_choices[] = $choice->toArray();
      } else {
        throw new \BgaVisibleSystemException('Internal error: invalid type for parameter choice.');
      }
    }

    $resolve_value = $this->getParameterInner($world, $i, 'TILE', $json_choices, $cancellable);
    if ($resolve_value === null) {
      return null;
    }
    return $world->getTileByPos(Position::fromArray($resolve_value['pos']));
  }

  // As above, but elements of $choices must be `Entity`.
  protected function getEntityParameter(World $world, int $i, $choices, bool $cancellable = true): ?Entity
  {
    $json_choices = [];
    foreach ($choices as $choice) {
      if ($choice instanceof Entity) {
        $json_choices[] = $choice->id;
      } else {
        throw new \BgaVisibleSystemException('Internal error: invalid type for parameter choice.');
      }
    }

    $resolve_value = $this->getParameterInner($world, $i, 'ENTITY', $json_choices, $cancellable);
    if ($resolve_value === null) {
      return null;
    }
    return Entity::getById($world, $resolve_value['entity']);
  }

  // As above, but elements of $choices must be `PlayerCharacter`.
  //
  // Right now, this is a thin layer over `getEntityParameter()`.
  //
  // TODO: We probably want UI that explicit asks the player to pick a
  // PC target, rather than asking the player to go find a PC entity on
  // the board somewhere.  Depending on how the UI side works out, we
  // may need to tweak how we send target-selection metadata from the
  // server.
  protected function getPcParameter(World $world, int $i, $choices, bool $cancellable = true): ?PlayerCharacter
  {
    $entity_choices = array_map(function ($pc) {
      assert($pc instanceof PlayerCharacter);
      return $pc->entity;
    }, $choices);

    $pc_entity = $this->getEntityParameter($world, $i, $entity_choices);

    return $pc_entity === null ? null : $world->getPlayerCharacterByEntityId($pc_entity->id);
  }

  // As above, but elements of $choices must be `Wall`.
  protected function getWallParameter(World $world, int $i, $choices, bool $cancellable = true): ?Wall
  {
    throw new \BgaUserException('no impl');
  }

  // XXX: I'm not sure about the signature of this one yet.
  //
  // Currently, the keys "title" and "value" are required; the key
  // "image" is optional.
  //
  // The return value is one of the items' "value" property.
  protected function getCustomParameter(World $world, int $i, $choices, bool $cancellable = true)
  {
    // XXX: Validate that each item contains the keys that we expect.
    $json_choices = $choices;

    $resolve_value = $this->getParameterInner($world, $i, 'CUSTOM', $json_choices, $cancellable);
    if ($resolve_value === null) {
      return null;
    }
    return $resolve_value['customValue'];
  }
}
