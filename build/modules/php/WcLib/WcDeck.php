<?php

namespace BurgleBrosTwo\Managers;

// XXX: There's a lot of overlap between this and `CardPeer`.
class Card
{
  // XXX: This is a leftover from our migration to a `Card` type;
  // need to finish that migration.
  public $row;

  private int $id_;
  private string $type_;
  private string $type_group_;
  private string $location_;
  private string $sublocation_;
  private int $location_index_;
  private int $order_;
  private int $use_count_;

  public static function fromRow($row): ?Card
  {
    if ($row === null) {
      return null;
    }

    $card = new Card();
    $card->row = $row;
    $card->id_ = intval($row['id']);
    $card->type_ = $row['card_type'];
    $card->type_group_ = $row['card_type_group'];
    $card->location_ = $row['card_location'];
    $card->sublocation_ = $row['card_sublocation'];
    $card->location_index_ = intval($row['card_location_index']);
    $card->order_ = intval($row['card_order']);
    $card->use_count_ = intval($row['use_count']);
    return $card;
  }

  public function id(): int
  {
    return $this->id_;
  }

  public function type(): string
  {
    return $this->type_;
  }

  public function typeGroup(): string
  {
    return $this->type_group_;
  }

  public function location(): string
  {
    return $this->location_;
  }

  public function sublocation(): string
  {
    return $this->sublocation_;
  }

  public function locationIndex(): int
  {
    return $this->location_index_;
  }

  public function order(): int
  {
    return $this->order_;
  }

  public function useCount(): int
  {
    return $this->use_count_;
  }

  // Below this line are BurgleBrosTwo-specific helpers.

  public function cardTypeData()
  {
    return CARD_DATA[$this->type_group_][$this->type_];
  }

  public function maxUses(): int
  {
    return $this->cardTypeData()['uses'] ?? 1;
  }

  public function title(): string
  {
    return $this->cardTypeData()['title'];
  }

  public function image(): string
  {
    return $this->cardTypeData()['image'];
  }

  public function back(): string
  {
    return $this->cardTypeData()['back'];
  }

  public function flipped(): bool
  {
    if ($this->typeGroup() != 'gear') {
      throw new \BgaVisibleSystemException('Internal error: flipped() only makes sense for gear cards.');
    }
    return $this->useCount() >= $this->maxUses();
  }

  public function effectiveType(): string
  {
    if ($this->flipped()) {
      return $this->back();
    } else {
      return $this->type();
    }
  }
}

class CardManager extends \APP_DbObject
{
  // N.B.: For `card_order`, lower numbers are "first" (closer to
  // the top of a deck).

  // See `card` table schema for details.
  protected $card_location;
  protected $card_location_index;

  function __construct($card_location, $card_location_index = null)
  {
    parent::__construct();

    $this->card_location = $card_location;
    $this->card_location_index = $card_location_index;
  }

  // --- API ---

  function cardLocation()
  {
    return $this->card_location;
  }
  function cardLocationIndex()
  {
    return $this->card_location_index;
  }

  // Instantiates new cards per $card_specs, assigning them new card
  // IDs in random order.  The new cards are placed on bottom of
  // $card_sublocation with shuffled $card_order.
  //
  // TODO: If it's not too hard, only shuffle the new cards; but we
  // don't really need that functionality, so it is probably okay to
  // shuffle the whole thing.
  //
  // TODO: What should $card_specs be?  Right now, it's an array;
  // each individual card spec is an associative array with one key
  // ("card_type").
  function createCards($card_specs, $card_sublocation = 'DECK')
  {
    // XXX: this isn't going to work when `card_location_index` is NULL yet
    //
    // XXX: Also, we need to `$this->shuffle()` the new cards
    // (since all of them are created with a card_order of -1).
    // We should move that into this function!

    $values = [];
    foreach ($card_specs as $card_spec) {
      // XXX: update some of these values
      $values[] =
        '("patrol", "' . $card_spec['card_type'] . '", "PATROL", "DECK", ' . $this->card_location_index . ', -1' . ')';
    }

    shuffle($values);
    $sql =
      'INSERT INTO card (`card_type_group`, `card_type`, `card_location`, `card_sublocation`, `card_location_index`, `card_order`) VALUES ' .
      implode(',', $values);
    self::DbQuery($sql);
  }

  // Takes cards from all $card_sublocations and moves them to
  // $destination_sublocation.
  function moveAll($card_sublocations, $destination_sublocation = 'DECK'): void
  {
    foreach ($this->rawGetAll($card_sublocations) as $card) {
      $this->placeOnTop($card, $destination_sublocation);
    }
  }

  // // XXX: Instead, create a CardManager with the intended
  // // location & location_index and use `placeOn{Top,Bottom}()`
  //
  // function move($card_id, $destination_location, $destination_sublocation, $destination_location_index = null) {
  //     $update_subexprs = [
  //         'card_location = "'.$destination_location.'"',
  //         'card_sublocation = "'.$destination_sublocation.'"',
  //     ];
  //     if (!is_null($destination_location_index)) {
  //         $update_subexprs[] = 'card_location_index = '.$destination_location_index;
  //     }
  //     self::DbQuery('UPDATE `card` SET ' . implode(',', $update_subexprs) . ' WHERE `id` = ' . $card_id);
  // }

  // Randomly assign a new `card_order` value to each card in
  // $card_sublocation.
  function shuffle($card_sublocation = 'DECK')
  {
    self::trace('CardManager: shuffle()');
    $cards = $this->rawGetAll([$card_sublocation]);
    shuffle($cards);

    $i = 0; // XXX: Should be able to replace this with `foreach()` syntax.
    foreach ($cards as $card) {
      self::trace('CardManager: shuffle(): setting card_order=' . $i . ' for id=' . $card['id']);
      self::DbQuery('UPDATE `card` SET card_order=' . $i . ' WHERE `id` = ' . $card['id']);
      ++$i;
    }
  }

  function rawGetAll($card_sublocations = ['DECK'])
  {
    return self::getCollectionFromDb('SELECT * FROM `card` WHERE ' . $this->buildWhereClause($card_sublocations));
  }

  function getAll($card_sublocations = ['DECK'])
  {
    return array_map(function ($row) {
      return Card::fromRow($row);
    }, $this->rawGetAll($card_sublocations));
  }

  function get(int $card_id): Card
  {
    return Card::fromRow($this->rawGet($card_id));
  }

  function rawGet($cardId)
  {
    $this->assertValidCardId($cardId);

    self::trace("CardManager::rawGet(cardId={$cardId})");
    // XXX: this should probably return an error if the card is not within the scope of this CardManager
    $card = self::getObjectFromDB('SELECT * FROM `card` WHERE `id` = ' . $cardId);
    if (is_null($card['id'])) {
      throw new \BgaUserException(
        "CardManager::rawGet(cardId={$cardId}) -- card ID is null; $card=" . print_r($card, true)
      );
    }
    return $card;
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  function rawGetAllOfType($card_type)
  {
    return $this->getCollectionFromDB('SELECT * FROM `card` WHERE `card_type` = "' . $card_type . '"');
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  function rawGetAllOfTypeGroup(string $card_type_group)
  {
    return $this->getCollectionFromDB('SELECT * FROM `card` WHERE `card_type_group` = "' . $card_type_group . '"');
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  function getAllOfTypeGroup(string $card_type_group)
  {
    return array_map(function ($row) {
      return Card::fromRow($row);
    }, $this->rawGetAllOfTypeGroup($card_type_group));
  }

  // Returns the top `Card` in the indicated $card_sublocation, or
  // `null` iff it is empty.
  function rawPeekTop($card_sublocation = 'DECK')
  {
    $sql =
      'SELECT * FROM `card` WHERE ' . $this->buildWhereClause([$card_sublocation]) . ' ORDER BY card_order ASC LIMIT 1';
    // self::trace("readMinCardOrder: {$sql}");
    return $this->getObjectFromDB($sql);
  }

  // Returns the top `Card` in the indicated $card_sublocation, or
  // `null` iff it is empty.
  function peekTop($card_sublocation = 'DECK'): ?Card
  {
    return Card::fromRow($this->rawPeekTop($card_sublocation));
  }

  // Returns the top `Card` in the indicated $card_sublocation, and
  // moves it to $destination_sublocation, where it is placed on top.
  //
  // If the deck is empty, if $auto_reshuffle is true and there are
  // cards in $destination_sublocation, `moveAll()` them back to
  // $card_sublocation and then `shuffle()` them and try again.  If
  // $auto_reshuffle is false, or if there are no cards in either
  // sublocation, returns `null`.
  function rawDrawAndDiscard($card_sublocation = 'DECK', $destination_sublocation = 'DISCARD', $auto_reshuffle = false)
  {
    $card = $this->rawPeekTop($card_sublocation);

    if ($card === null) {
      if (!$auto_reshuffle) {
        return null;
      }
      $this->moveAll([$destination_sublocation], $card_sublocation);
      $this->shuffle($card_sublocation);
      return $this->rawDrawAndDiscard($card_sublocation, $destination_sublocation, /*auto_reshuffle=*/ false);
    }

    $this->placeOnTop($card, $destination_sublocation);
    // XXX: should $card reflect the before position or the after position?
    return $card;
  }

  function drawAndDiscard(
    $card_sublocation = 'DECK',
    $destination_sublocation = 'DISCARD',
    $auto_reshuffle = false
  ): ?Card {
    $row = $this->rawDrawAndDiscard($card_sublocation, $destination_sublocation, $auto_reshuffle);
    return Card::fromRow($row);
  }

  // Like `drawAndDiscard()`, but repeats until $predicate returns
  // true for a card.  Cards that do not match are placed in
  // $destination_sublocation.
  function rawDrawAndDiscardUntil(
    $predicate,
    $card_sublocation = 'DECK',
    $destination_sublocation = 'DISCARD',
    $auto_reshuffle = false
  ) {
    throw new \BgaUserException('not implemented');
  }

  // Like `drawAndDiscard()`, but repeats until $predicate returns
  // true for a card.  Cards that do not match remain in
  // $card_sublocation.
  //
  // If no card in $card_sublocation matches, returns null.
  //
  // Assumes $auto_reshuffle=false, mostly for implementation
  // convenience.  Could be extended to support that.
  function rawDrawAndDiscardFirstMatching($predicate, $card_sublocation = 'DECK', $destination_sublocation = 'DISCARD')
  {
    $cards = $this->rawGetAll([$card_sublocation]);

    foreach ($cards as $card) {
      if ($predicate($card)) {
        $this->placeOnTop($card, $destination_sublocation);
        return $card;
      }
    }

    return null;
  }

  function assertValidCard($card)
  {
    // XXX: This is a leftover from our migration to a `Card` type;
    // need to finish that migration.
    if ($card instanceof Card) {
      $card = $card->row;
    }

    $this->assertValidCardId($card['id']);
  }

  function assertValidCardId($cardId)
  {
    // // XXX: This is a string that looks like e.g. "5".
    // if (!is_int($card['id']) || $card['id'] <= 0) {
    //     throw new \BgaUserException("CardManager::placeOnTop(): invalid cardId: {$card['id']} which is a " . get_debug_type($card['id']));
    // }
  }

  function placeOnTop($card, $card_sublocation)
  {
    // XXX: This is a leftover from our migration to a `Card` type;
    // need to finish that migration.
    if ($card instanceof Card) {
      $card = $card->row;
    }

    $this->assertValidCard($card);
    $this->shiftCardOrder($card_sublocation, 1);
    $this->updateCard($card, $card_sublocation, /*card_order=*/ 0);
  }

  function placeOnBottom($card, $card_sublocation)
  {
    // XXX: This is a leftover from our migration to a `Card` type;
    // need to finish that migration.
    if ($card instanceof Card) {
      $card = $card->row;
    }

    $this->assertValidCard($card);
    $this->updateCard($card, $card_sublocation, /*card_order=*/ $this->readMaxCardOrder($card_sublocation) + 1);
  }

  // --- Internal helpers ---

  function updateCard($card, $card_sublocation, $card_order)
  {
    // XXX: This is a leftover from our migration to a `Card` type;
    // need to finish that migration.
    if ($card instanceof Card) {
      $card = $card->row;
    }

    $this->assertValidCard($card);
    $update_subexprs = [
      'card_location = "' . $this->card_location . '"',
      'card_sublocation = "' . $card_sublocation . '"',
      'card_order = ' . $card_order,
    ];
    if (!is_null($this->card_location_index)) {
      $update_subexprs[] = 'card_location_index = ' . $this->card_location_index;
    } else {
      $update_subexprs[] = 'card_location_index = NULL';
    }
    self::DbQuery('UPDATE `card` SET ' . implode(',', $update_subexprs) . ' WHERE `id` = ' . $card['id']);
  }

  // Modifies all `card_order`s in $card_sublocation by $n.
  protected function shiftCardOrder($card_sublocation, $n)
  {
    self::DbQuery(
      'UPDATE `card` SET card_order=(card_order+' . $n . ') WHERE ' . $this->buildWhereClause([$card_sublocation])
    );
  }

  // Returns the number of cards in $card_sublocation.
  protected function cardCount($card_sublocation)
  {
    return self::getUniqueValueFromDB(
      'SELECT COUNT(*) FROM `card` WHERE ' . $this->buildWhereClause([$card_sublocation])
    );
  }

  protected function readMaxCardOrder($card_sublocation)
  {
    return self::getUniqueValueFromDB(
      'SELECT card_order FROM `card` WHERE ' .
        $this->buildWhereClause([$card_sublocation]) .
        ' ORDER BY card_order DESC LIMIT 1'
    );
  }

  protected function readMinCardOrder($card_sublocation)
  {
    $sql =
      'SELECT card_order FROM `card` WHERE ' .
      $this->buildWhereClause([$card_sublocation]) .
      ' ORDER BY card_order ASC LIMIT 1';
    self::trace("readMinCardOrder: {$sql}");
    return self::getUniqueValueFromDB($sql);
  }

  protected function buildWhereClause($card_sublocations)
  {
    $clause = 'card_location = "' . $this->card_location . '"';

    if (!is_null($this->card_location_index)) {
      $clause .= ' AND card_location_index = ' . $this->card_location_index;
    }

    $sublocation_values = [];
    foreach ($card_sublocations as $card_sublocation) {
      $sublocation_values[] = '"' . $card_sublocation . '"';
    }
    if (count($sublocation_values) > 0) {
      $clause .= ' AND card_sublocation IN (' . implode(',', $sublocation_values) . ')';
    }

    return $clause;
  }
}
