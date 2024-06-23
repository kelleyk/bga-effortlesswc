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

namespace EffortlessWC\Test;

require_once '/src/localarena/module/test/IntegrationTestCase.php';

// XXX: Necessary for Phan until we sort out path structure once and for all.
if (!defined('LOCALARENA_GAME_PATH')) {
  define('LOCALARENA_GAME_PATH', '/src/game/');
}

require_once LOCALARENA_GAME_PATH . 'effortlesswc/modules/php/constants.inc.php';
require_once LOCALARENA_GAME_PATH . 'effortlesswc/modules/php/WcLib/WcDeck.php';

use LocalArena\TableParams;

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
  const LOCALARENA_GAME_NAME = 'effortlesswc';

  function setupCleanState(): void
  {
    $params = new TableParams();
    $params->playerCount = 1;
    $this->initTable($params);

    //   $this->assertGameState(ST_CHARACTER_SELECTION);

    //   $player = $this->playerByIndex(0);
    //   for ($i = 0; $i < 2; $i++) {
    //     $card = array_value_first($this->gamestate()['args']['cards']);
    //     $player->act('actPlayCard', [
    //       'cardId' => $card['id'],
    //     ]);
    //   }
    //   $player->act('actPass');
    //   $this->assertGameState(ST_PLACE_ENTRANCE_TOKENS);

    //   $player->act('actSelectTile', ['pos' => '0,0,0']);
    //   $player->act('actSelectTile', ['pos' => '3,3,0']);
    //   $this->assertGameState(ST_PLAYER_TURN_ENTER_MAP);

    //   // Wipe the board clean, so that there isn't anything
    //   // interesting in the game beyond what each test case creates.
    //   $this->table()->DbQuery('UPDATE `tile` SET `tile_type` = "' . TILETYPE_NOOP . '", `tile_number` = NULL WHERE TRUE');
    //   $this->table()->DbQuery('DELETE FROM `wall` WHERE TRUE');
    //   $this->table()->DbQuery('DELETE FROM `character_npc` WHERE TRUE');
    //   $this->table()->DbQuery('DELETE FROM `entity` WHERE `entity_type` NOT IN ("CHARACTER_PLAYER", "TOKEN_ENTRANCE")');

    //   $player->act('actSelectTile', ['pos' => '0,0,0']);
    //   $this->assertGameState(ST_PLAYER_TURN);

    //   // Remove gear cards from player character hands.
    //   $gear = new CardManager('GEAR');
    //   // XXX: once we fix the non-static methods that should be static, this should be 'CardManager::...'
    //   foreach ($gear->rawGetAllOfTypeGroup('gear') as $gear_card) {
    //     $gear->placeOnTop($gear_card, 'DECK');
    //   }

    echo "\n" . '** Done with `setupCleanState()`; starting actual test case.' . "\n\n";
  }

  // public function activeCharacter(): CharacterPeer
  // {
  //   $row = $this->table()->rawGetActivePlayerCharacter();
  //   return new CharacterPeer($this, $row);
  // }

  // public function assertActive(CharacterPeer $pc): void
  // {
  //   $this->assertEquals($this->activeCharacter()->id(), $pc->id(), 'Expected given PC to be active.');
  // }

  // public function character(int $id): CharacterPeer
  // {
  //   $row = $this->table()->getObjectFromDB('SELECT * FROM `character_player` WHERE `id` = ' . $id);
  //   return new CharacterPeer($this, $row);
  // }

  // // $order==0 will return the PC who took the first turn, and so on.
  // // This does not necessarily match the character ID order.
  // public function characterByTurnOrder(int $order_index): CharacterPeer
  // {
  //   $pcs_in_turn_order = [];
  //   $pc = $this->table()->getNextPlayerCharacterInTurnOrder(-1);
  //   while (!in_array(intval($pc['id']), $pcs_in_turn_order)) {
  //     $pcs_in_turn_order[] = intval($pc['id']);
  //     $pc = $this->table()->getNextPlayerCharacterInTurnOrder($pc['turn_order']);
  //   }

  //   if ($order_index < 0 || $order_index > count($pcs_in_turn_order)) {
  //     throw new \BgaUserException('Invalid turn-order index.');
  //   }
  //   return $this->character($pcs_in_turn_order[$order_index]);
  // }

  // public function npc(int $id): NpcPeer
  // {
  //   $row = $this->table()->rawGetNpc($id);
  //   return new NpcPeer($this, $row);
  // }

  // public function card(int $id): CardPeer
  // {
  //   $row = $this->table()->rawGetCard($id);
  //   return new CardPeer($this, $row);
  // }

  // public function entity(int $id): EntityPeer
  // {
  //   $row = $this->table()->getObjectFromDB('SELECT * FROM `entity` WHERE `id` = ' . $id);
  //   return new EntityPeer($this, $row);
  // }

  // public function entitiesByPos(Position $pos, ?string $entity_type)
  // {
  //   $entities = [];
  //   foreach ($this->table()->rawGetEntitiesByPos($pos) as $row) {
  //     if ($entity_type === null || $row['entity_type'] == $entity_type) {
  //       $entities[] = new EntityPeer($this, $row);
  //     }
  //   }
  //   return $entities;
  // }

  // public function entities(?string $entity_type, bool $include_despawned = false)
  // {
  //   $entities = [];
  //   foreach ($this->table()->rawGetEntities($entity_type) as $row) {
  //     $entity = new EntityPeer($this, $row);
  //     if ($include_despawned || $entity->pos() !== null) {
  //       $entities[] = $entity;
  //     }
  //   }
  //   return $entities;
  // }

  // public function tile($pos): TilePeer
  // {
  //   $pos = $this->toPosition($pos);
  //   $row = $this->table()->rawGetTileByPos($pos);
  //   return new TilePeer($this, $row);
  // }

  // public function tileById(int $id): TilePeer
  // {
  //   $row = $this->table()->rawGetTile($id);
  //   return new TilePeer($this, $row);
  // }

  // // Returns `TilePeer[]`.
  // public function tilesByType(string $tile_type)
  // {
  //   $result = [];
  //   foreach ($this->table()->rawGetTilesByType($tile_type) as $row) {
  //     $result[] = new TilePeer($this, $row);
  //   }
  //   return $result;
  // }

  // public function createEntity(string $entity_type, $pos): EntityPeer
  // {
  //   $pos = $this->toPosition($pos);
  //   $entity_id = $this->table()->createEntity($entity_type, $pos, /*msg=*/ '', /*state=*/ 'HIDDEN', /*silent=*/ true);
  //   return $this->entity($entity_id);
  // }

  // public function createBouncer($pos, $destination): NpcPeer
  // {
  //   $pos = $this->toPosition($pos);
  //   $destination = $this->toPosition($destination);
  //   $npc_id = $this->table()->spawnNpc('BOUNCER', $pos, $destination);
  //   return $this->npc($npc_id);
  // }

  // public function createWall($pos, bool $vertical): void
  // {
  //   $pos = $this->toPosition($pos);
  //   $this->table()->createWall($pos, $vertical);
  // }

  // public function triggerCommotion($pos, int $moves = 1, bool $stop_at_dest = false): void
  // {
  //   $pos = $this->toPosition($pos);
  //   $this->table()->triggerCommotion($pos, $moves, $stop_at_dest);
  //   $this->table()->gamestate->nextState('tResolveEffects');
  // }

  // public function setStepping(PlayerPeer $player, bool $stepping)
  // {
  //   $player->act('actChangeGameFlowSettings', [
  //     'stepping' => $stepping,
  //   ]);
  // }

  // // $dice should be an array of ints in [1..6].
  // public function setDiceResult($dice): void
  // {
  //   $dice_stub = $this->createStub(\EffortlessWC\Utilities\DiceRoller::class);
  //   $dice_stub->method('roll')->willReturn($dice);
  //   $this->table()->dice_roller = $dice_stub;
  // }

  // public function assertEntityDiscarded(EntityPeer $entity): void
  // {
  //   $this->assertEquals('VISIBLE', $entity->state());
  //   $this->assertEquals(null, $entity->pos());
  // }

  // // Has the second PC enter the map.  When this function returns, it
  // // is the first PC's turn again.  ("First" and "second" here refer
  // // to turn order, which is not necessarily character index order.)
  // public function setup_secondPcEntersMap(): void
  // {
  //   $pc0 = $this->activeCharacter();
  //   $pc0->pass();

  //   // The second character enters the map.
  //   $pc1 = $this->activeCharacter();
  //   $this->assertGameState(ST_PLAYER_TURN_ENTER_MAP);
  //   $pc1->act('actSelectTile', ['pos' => '0,0,0']);
  //   $this->assertGameState(ST_PLAYER_TURN);
  //   $pc1->pass();
  // }

  // // $deck_name must be one of 'POOL' or 'LOUNGE'.  Puts the event
  // // cards with the given types on top of that deck, in the order
  // // given.
  // public function setEventDeck($deck_name, $top_cards, $sublocation = 'DECK'): void
  // {
  //   if ($deck_name != 'POOL' && $deck_name != 'LOUNGE') {
  //     throw new \feException('Unexpected event deck name.');
  //   }

  //   $event_deck = new CardManager($deck_name);

  //   foreach (array_reverse($top_cards) as $card_type) {
  //     $cards = $event_deck->rawGetAllOfType($card_type);
  //     if (count($cards) != 1) {
  //       throw new \BgaUserException('Unexpected number of cards of type.');
  //     }

  //     echo '*** Placing ' . $card_type . ' on top...' . "\n";
  //     foreach ($cards as $card) {
  //       $card = $event_deck->rawGet($card['id']);
  //       $event_deck->placeOnTop($card, $sublocation);
  //     }
  //   }
  // }

  // // Asserts that the game is in ST_TARGET_SELECTION and that the
  // // choices offered match $expected_choices.  Each element in
  // // $expected_choices may be a `TilePeer`, a `Position`, or a
  // // `Position`-like array.  The order of elements in $choices does
  // // not matter.
  // public function assertTileParameterChoices($expected_choices)
  // {
  //   $this->assertGameState(ST_TARGET_SELECTION);
  //   $target_args = $this->activePlayer()->state()->args()['target'];
  //   $this->assertEquals('TILE', $target_args['valueType']);

  //   $actual_choices = array_map(function ($x) {
  //     return Position::fromArray($x);
  //   }, $target_args['choices']);
  //   $expected_choices = array_map(function ($x) {
  //     return $this->toPosition($x);
  //   }, $expected_choices);

  //   $this->assertEqualsCanonicalizing($expected_choices, $actual_choices);
  // }

  // // As above, but for entity target selection.  Each element of
  // // $expected_choices may be an `EntityPeer` or an integer entity
  // // ID.
  // public function assertEntityParameterChoices($expected_choices)
  // {
  //   throw new Exception('Not implemented.');
  // }

  // // As above, but for entity target selection.  Each element of
  // // $expected_choices may be a `CharacterPeer` or an integer PC
  // // ID.
  // public function assertPcParameterChoices($expected_choices)
  // {
  //   throw new Exception('Not implemented.');
  // }

  // // As above, but for "custom" target selection.  Each element of
  // // $expected_choices must be an array.  See `getCustomParameter()`
  // // for a description of what each array must contain.
  // public function assertCustomParameterChoices($expected_choices)
  // {
  //   $this->assertGameState(ST_TARGET_SELECTION);
  //   $target_args = $this->activePlayer()->state()->args()['target'];
  //   $this->assertEquals('CUSTOM', $target_args['valueType']);

  //   $actual_choices = $target_args['choices'];

  //   $this->assertEqualsCanonicalizing($expected_choices, $actual_choices);
  // }

  // // Accepts `Position`s, `TilePeer`s, and `Position`-like arrays;
  // // converts them into `Position`s.
  // public function toPosition($pos): ?Position
  // {
  //   if ($pos === null) {
  //     return null;
  //   }
  //   if (is_array($pos)) {
  //     $pos = Position::fromArray($pos);
  //   }
  //   if ($pos instanceof TilePeer) {
  //     $pos = $pos->pos();
  //   }
  //   return $pos;
  // }
}

// // XXX: Rename; this is for player characters.
// class CharacterPeer
// {
//   private IntegrationTestCase $itc_;

//   private int $id_;
//   private string $player_id_;
//   private int $entity_id_;
//   private int $heat_;

//   private function table()
//   {
//     return $this->itc_->table();
//   }

//   public function __construct($itc, $row)
//   {
//     if ($row === null) {
//       throw new \BgaUserException('$row is null');
//     }

//     $this->itc_ = $itc;

//     $this->id_ = intval($row['id']);
//     $this->player_id_ = $row['player_id'];
//     $this->entity_id_ = intval($row['entity_id']);
//     $this->heat_ = intval($row['heat']);
//   }

//   public function id(): int
//   {
//     return $this->id_;
//   }

//   public function player(): PlayerPeer
//   {
//     return $this->itc_->playerById($this->player_id_);
//   }

//   public function pos(): Position
//   {
//     return $this->entity()->pos();
//   }

//   public function heat(): int
//   {
//     return $this->heat_;
//   }

//   public function setHeat(int $heat): void
//   {
//     $this->heat_ = $heat;
//     $this->table()->updatePc($this->id(), [
//       'heat' => $this->heat_,
//     ]);
//   }

//   public function entity(): EntityPeer
//   {
//     $row = $this->table()->rawGetEntity($this->entity_id_);
//     return new EntityPeer($this->itc_, $row);
//   }

//   public function move($pos): void
//   {
//     if (is_array($pos)) {
//       $pos = Position::fromArray($pos);
//     }
//     $this->act('actMove', ['pos' => $pos->toNumberList()]);
//   }

//   public function peek($pos): void
//   {
//     if (is_array($pos)) {
//       $pos = Position::fromArray($pos);
//     }
//     $this->act('actPeek', ['pos' => $pos->toNumberList()]);
//   }

//   public function pass(): void
//   {
//     $this->act('actPass');
//   }

//   public function specialAction($params): void
//   {
//     $this->act('actSpecialAction', ['specialAction' => $params]);
//   }

//   public function cancel(): void
//   {
//     $this->act('actCancel');
//   }

//   public function playCard($card_id, ...$targets): void
//   {
//     if ($card_id instanceof CardPeer) {
//       $card_id = $card_id->id();
//     }
//     if (!is_int($card_id)) {
//       throw new \BgaUserException('Unexpected type for $card_id parameter.');
//     }

//     $this->act('actPlayCard', ['cardId' => $card_id]);
//     $this->selectTarget(...$targets);
//   }

//   // N.B.: This does not work for "CUSTOM" parameters.
//   public function selectTarget(...$targets): void
//   {
//     foreach ($targets as $target) {
//       if ($target instanceof CharacterPeer) {
//         // N.B.: Right now these are sent as entities, but if we
//         // wind up adding a player-character target type, we can
//         // tweak that here!
//         $this->act('actSelectTarget', [
//           'target' => [
//             'entity' => $target->entity()->id(),
//           ],
//         ]);
//       } elseif ($target instanceof EntityPeer) {
//         $this->act('actSelectTarget', [
//           'target' => [
//             'entity' => $target->id(),
//           ],
//         ]);
//       } elseif ($target instanceof Position) {
//         $this->act('actSelectTarget', [
//           'target' => [
//             'tile' => $target->toArray(),
//           ],
//         ]);
//       } else {
//         // XXX: We probably need a better exception type here.
//         throw new \BgaUserException('Unexpected type for target object: ' . gettype($target));
//       }
//     }
//   }

//   public function act(string $action_name, $action_args = []): void
//   {
//     echo 'Character ' . $this->id() . ' performing action "' . $action_name . '"...' . "\n";

//     // For AT_json args.
//     foreach ($action_args as $k => $v) {
//       if (is_array($action_args[$k])) {
//         $action_args[$k] = json_encode($action_args[$k]);
//       }
//     }

//     $this->table()->doAction(
//       $this->table()->gameServer,
//       array_merge($action_args, [
//         'bgg_actionName' => $action_name,
//         'bgg_player_id' => $this->player()->id(),
//       ])
//     );
//   }

//   // Returns `Card[]`.
//   public function unpreppedGear()
//   {
//     $pc_cards = new CardManager('CHARACTER', $this->id());
//     return $pc_cards->getAll(['HAND']);
//   }

//   // Returns `Card[]`.
//   public function preppedGear()
//   {
//     $pc_cards = new CardManager('CHARACTER', $this->id());
//     return $pc_cards->getAll(['PREPPED']);
//   }

//   public function setPos($pos): void
//   {
//     $this->entity()->setPos($pos);
//   }

//   public function refreshState(): CharacterPeer
//   {
//     return $this->itc_->character($this->id());
//   }

//   public function giveGearCard(string $card_type, bool $prepped = true): CardPeer
//   {
//     $gear = new \EffortlessWC\Managers\CardManager('GEAR');
//     $pc_cards = new \EffortlessWC\Managers\CardManager('CHARACTER', $this->id());

//     $n = 0;
//     $card_id = null;
//     // XXX: Should we use `getAllOfTypeGroup()` here instead,
//     // rather than assuming that this gear card is in the deck?
//     foreach ($gear->rawGetAll(['DECK']) as $gear_card) {
//       if ($gear_card['card_type'] == $card_type) {
//         ++$n;
//         $pc_cards->placeOnTop($gear_card, $prepped ? 'PREPPED' : 'HAND');
//         $card_id = intval($gear_card['id']);
//       }
//     }

//     if ($n != 1) {
//       throw new \BgaUserException('Expected exactly one gear card of type.');
//     }
//     if ($card_id === null) {
//       throw new \BgaUserException('Why is that null?');
//     }

//     return $this->itc_->card($card_id);
//   }

//   public function drawEventCard(string $event_deck): void
//   {
//     if ($event_deck != 'POOL' && $event_deck != 'LOUNGE') {
//       throw new \feException('Unexpected event deck name.');
//     }

//     // XXX: This is cribbed from `EventTile::onPcEnters()`.
//     $this->table()->pushOnResolveStack([
//       [
//         'effectType' => 'draw-event-card',
//         'eventDeck' => $event_deck,
//         'pos' => $this->pos()->toArray(),
//         'pcId' => $this->id(),
//       ],
//     ]);
//     $this->table()->gamestate->nextState('tResolveEffects');
//   }
// }

// class NpcPeer
// {
//   private IntegrationTestCase $itc_;

//   private int $id_;
//   private int $entity_id_;
//   private int $destination_entity_id_;

//   private function table()
//   {
//     return $this->itc_->table();
//   }

//   public function __construct($itc, $row)
//   {
//     $this->itc_ = $itc;
//     $this->id_ = intval($row['id']);
//     $this->entity_id_ = intval($row['entity_id']);
//     $this->destination_entity_id_ = intval($row['destination_entity_id']);
//   }

//   public function id(): int
//   {
//     return $this->id_;
//   }

//   public function pos(): Position
//   {
//     return $this->entity()->pos();
//   }

//   public function destination(): ?Position
//   {
//     $entity = $this->destination_entity();
//     return $entity === null ? null : $entity->pos();
//   }

//   public function entity(): EntityPeer
//   {
//     $row = $this->table()->rawGetEntity($this->entity_id_);
//     return new EntityPeer($this->itc_, $row);
//   }

//   public function destination_entity(): ?EntityPeer
//   {
//     $row = $this->table()->rawGetEntity($this->destination_entity_id_);
//     return $row === null ? null : new EntityPeer($this->itc_, $row);
//   }

//   public function setPos($pos): void
//   {
//     $this->entity()->setPos($pos);
//   }

//   public function setDestination($pos): void
//   {
//     $this->destination_entity()->setPos($pos);
//   }

//   // Puts the patrol cards corresponding to $top_destinations on top
//   // of the patrol deck for this bouncer's floor, in the order
//   // given.
//   public function setPatrolDeck($top_destinations, $sublocation = 'DECK'): void
//   {
//     $patrol_deck = new CardManager('PATROL', $this->pos()->z);

//     foreach (array_reverse($top_destinations) as $destination) {
//       if ($destination === 'distracted') {
//         $card_type = 'distracted';
//       } else {
//         $destination = $this->itc_->toPosition($destination);
//         // XXX: This should be a library function we can reuse.
//         $card_type = 'patrol_' . $destination->x . '_' . $destination->y . '_' . $destination->z;
//       }

//       $cards = $patrol_deck->rawGetAllOfType($card_type);
//       if (count($cards) < 1) {
//         throw new \BgaUserException('Unexpected number of cards of type.');
//       }

//       $card = $patrol_deck->rawGet(end($cards)['id']);
//       $patrol_deck->placeOnTop($card, $sublocation);
//     }
//   }

//   public function startHunting(): void
//   {
//     $world = $this->table();
//     $npc = Npc::getById($world, $this->id());
//     $npc->startHunting($world);
//   }

//   public function addStatus($status): void
//   {
//     $world = $this->table();
//     $npc = Npc::getById($world, $this->id());
//     $world->addStatusToNpc($npc, $status);
//   }

//   public function refreshState(): NpcPeer
//   {
//     return $this->itc_->npc($this->id());
//   }
// }

// class EntityPeer
// {
//   private IntegrationTestCase $itc_;

//   private int $id_;
//   private ?Position $pos_;
//   private string $state_;
//   private string $entity_type_;

//   private function table()
//   {
//     return $this->itc_->table();
//   }

//   public function __construct($itc, $row)
//   {
//     $this->itc_ = $itc;

//     $this->id_ = intval($row['id']);
//     $this->pos_ = Position::fromRow($row);
//     $this->state_ = $row['state'];
//     $this->entity_type_ = $row['entity_type'];
//   }

//   public function refreshState(): EntityPeer
//   {
//     return $this->itc_->entity($this->id());
//   }

//   public function id(): int
//   {
//     return $this->id_;
//   }

//   // Returns null iff the entity is not on the board.
//   public function pos(): ?Position
//   {
//     return $this->pos_;
//   }

//   public function state(): string
//   {
//     return $this->state_;
//   }

//   // Returns one of the ENTITYTYPE_* constants.
//   public function entityType(): string
//   {
//     return $this->entity_type_;
//   }

//   public function setPos($pos): void
//   {
//     if (is_array($pos)) {
//       $pos = Position::fromArray($pos);
//     }

//     $this->pos_ = $pos;
//     $this->table()->updateEntity($this->id(), [
//       'pos' => $this->pos(),
//     ]);
//   }

//   public function setState(string $state): void
//   {
//     $this->state_ = $state;
//     $this->table()->updateEntity($this->id(), [
//       'state' => $this->state_,
//     ]);
//   }
// }

// class TilePeer
// {
//   private IntegrationTestCase $itc_;

//   private int $id_;
//   private Position $pos_;
//   private string $state_;
//   private string $tile_type_;
//   private ?int $tile_number_;
//   private int $counting_cubes_;

//   private function table()
//   {
//     return $this->itc_->table();
//   }

//   public function __construct($itc, $row)
//   {
//     $this->itc_ = $itc;

//     $this->id_ = intval($row['id']);
//     $this->pos_ = Position::fromRow($row);
//     $this->state_ = $row['state'];
//     $this->tile_type_ = $row['tile_type'];
//     $this->tile_number_ = intval($row['tile_number']);
//     $this->counting_cubes_ = intval($row['counting_cubes']);
//   }

//   public function id(): int
//   {
//     return $this->id_;
//   }

//   public function pos(): Position
//   {
//     return $this->pos_;
//   }

//   public function state(): string
//   {
//     return $this->state_;
//   }

//   public function setState(string $state): void
//   {
//     $this->state_ = $state;
//     $this->table()->updateTile($this->id(), [
//       'state' => $this->state(),
//     ]);
//   }

//   public function tileType(): string
//   {
//     return $this->tile_type_;
//   }

//   public function setTileType(string $tile_type): void
//   {
//     $this->tile_type_ = $tile_type;
//     $this->table()->updateTile($this->id(), [
//       'tile_type' => $this->tileType(),
//     ]);
//   }

//   public function tileNumber(): ?int
//   {
//     return $this->tile_number_;
//   }

//   public function setTileNumber(?int $tile_number): void
//   {
//     $this->tile_number_ = $tile_number;
//     $this->table()->updateTile($this->id(), [
//       'tile_number' => $this->tileNumber(),
//     ]);
//   }

//   public function countingCubes(): int
//   {
//     return $this->counting_cubes_;
//   }

//   public function setCountingCubes(int $counting_cubes): void
//   {
//     $this->counting_cubes_ = $counting_cubes;
//     $this->table()->updateTile($this->id(), [
//       'counting_cubes' => $this->countingCubes(),
//     ]);
//   }

//   // Returns `EntityPeer[]`.
//   public function entities(?string $entity_type)
//   {
//     return $this->itc_->entitiesByPos($this->pos(), $entity_type);
//   }

//   public function hasEntity(string $entity_type): bool
//   {
//     return count($this->entities($entity_type)) >= 1;
//   }

//   public function refreshState(): TilePeer
//   {
//     return $this->itc_->tileById($this->id());
//   }
// }

// class CardPeer
// {
//   private IntegrationTestCase $itc_;

//   private int $id_;
//   private string $type_;
//   private string $type_group_;
//   private string $location_;
//   private string $sublocation_;
//   private int $location_index_;
//   private int $order_;
//   private int $use_count_;

//   private function table()
//   {
//     return $this->itc_->table();
//   }

//   public function __construct($itc, $row)
//   {
//     $this->itc_ = $itc;

//     $this->id_ = intval($row['id']);
//     $this->type_ = $row['card_type'];
//     $this->type_group_ = $row['card_type_group'];
//     $this->location_ = $row['card_location'];
//     $this->sublocation_ = $row['card_sublocation'];
//     $this->location_index_ = intval($row['card_location_index']);
//     $this->order_ = intval($row['card_order']);
//     $this->use_count_ = intval($row['use_count']);
//   }

//   public function id(): int
//   {
//     return $this->id_;
//   }

//   public function type(): string
//   {
//     return $this->type_;
//   }

//   public function typeGroup(): string
//   {
//     return $this->type_group_;
//   }

//   public function location(): string
//   {
//     return $this->location_;
//   }

//   public function sublocation(): string
//   {
//     return $this->sublocation_;
//   }

//   public function locationIndex(): int
//   {
//     return $this->location_index_;
//   }

//   public function order(): int
//   {
//     return $this->order_;
//   }

//   public function useCount(): int
//   {
//     return $this->use_count_;
//   }

//   public function setUseCount(int $use_count): void
//   {
//     $this->use_count_ = $use_count;
//     $this->table()->updateCard($this->id(), [
//       'use_count' => $this->useCount(),
//     ]);
//   }

//   private function implCard(): \EffortlessWC\Managers\Card
//   {
//     $card_mgr = new \EffortlessWC\Managers\CardManager('no-such-location');
//     return $card_mgr->get($this->id());
//   }

//   public function flipped(): bool
//   {
//     return $this->implCard()->flipped();
//   }

//   public function refreshState(): CardPeer
//   {
//     return $this->itc_->card($this->id());
//   }
// }
