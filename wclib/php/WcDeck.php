<?php

namespace WcLib;

require_once 'CardBase.php';

class Card extends CardBase {
  // This is meant to be overridden by subclasses; but subclasses sometimes need to change its signature, which is why
  // it's not on `CardBase`.
  //
  // N.B.: This is a `string[]` because MySQL returns every column as a string, regardless of the column's actual type.
  // (XXX: Is this true for NULL values as well?)
  //
  // XXX: We really just want to say "this must return an instance of `get_called_class()` or null"; it should be
  // possible to do that without the template parameter.
  /**
    @template CardT of CardBase
    @param class-string<CardT> $CardT;
    @param string[]|null $row
    @return CardT|null
  */
  public static function fromRow(string $CardT, $row)
  {
    return self::fromRowBase($CardT, $row);
  }
}

// XXX: There's a lot of overlap between this and `CardPeer`.
//
// XXX: Make this `CardBase` instead?
class XXX_Legacy_Card
{
  private int $id_;
  private string $type_;
  private string $type_group_;
  private string $location_;
  private string $sublocation_;
  private int $sublocation_index_;
  private int $order_;

  // N.B.: This is a `string[]` because MySQL returns every column as a string, regardless of the column's actual type.
  // (XXX: Is this true for NULL values as well?)
  /** @param string[]|null $row */
  public static function fromRow($row): ?XXX_Legacy_Card
  {
    if ($row === null) {
      return null;
    }

    $card = new XXX_Legacy_Card();
    $card->id_ = intval($row['id']);
    $card->type_ = $row['card_type'];
    $card->type_group_ = $row['card_type_group'];
    $card->location_ = $row['card_location'];
    $card->sublocation_ = $row['card_sublocation'];
    $card->sublocation_index_ = intval($row['card_sublocation_index']);
    $card->order_ = intval($row['card_order']);
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

  public function sublocationIndex(): int
  {
    return $this->sublocation_index_;
  }

  public function order(): int
  {
    return $this->order_;
  }
}

// For `WcDeck` sublocation indices, "null" means "any/all".
const SUBLOCATION_INDEX_ANY = null;

/**
  @template CardT of CardBase

  The type of card that this deck contains.  This should be the base class of the CARD_TYPE_GROUP.  `\WcLib\Card` can be
  used in situations where no extra functionality is necessary.
*/
class WcDeck extends \APP_DbObject
{
  // N.B.: For `card_order`, lower numbers are "first" (closer to
  // the top of a deck).

  protected string $CardT_;

  // See `card` table schema for details.
  protected string $location_;

  /**
    @param class-string<CardT> $CardT;
   */
  function __construct(string $CardT, string $location)
  {
    // // XXX: BGA throws an error: "cannot call constructor" (so \APP_DbObject must not have a ctor).
    // parent::__construct();

    $this->CardT_ = $CardT;
    $this->location_ = $location;
  }

  // --- New (WcLib) API ---

  // XXX: Does not handle auto-reshuffling.
  /** @return CardT */
  public function drawTo(string $dest_sublocation, ?int $dest_sublocation_index, string $src_sublocation = 'DECK', ?int $src_sublocation_index = NULL)
  {
    $card = $this->peekTop($src_sublocation, $src_sublocation_index);
    if (is_null($card)) {
      throw new \BgaVisibleSystemException('No cards in ' . $this->location_ . ',' . $src_sublocation . ',' . (is_null($src_sublocation_index) ? 'NULL': $src_sublocation_index));
    }
    $this->placeOnTop($card, $dest_sublocation, $dest_sublocation_index);
    return $this->mustGet($card->id());
  }

  public function location(): string
  {
    return $this->location_;
  }

  // Randomly assign a new `card_order` value to each card in
  // $card_sublocation.
  public function shuffle($card_sublocation = 'DECK', ?int $sublocation_index = SUBLOCATION_INDEX_ANY)
  {
    self::trace('WcDeck: shuffle()');
    $cards = $this->rawGetAll([$card_sublocation], $sublocation_index);
    shuffle($cards);

    $i = 0; // XXX: Should be able to replace this with `foreach()` syntax.
    foreach ($cards as $card) {
      // XXX: When we have better control over log verbosity, we can probably un-comment this.
      //
      // self::trace('WcDeck: shuffle(): setting card_order=' . $i . ' for id=' . $card['id']);
      $this->DbQuery('UPDATE `card` SET card_order=' . $i . ' WHERE `id` = ' . $card['id']);
      ++$i;
    }
  }

  // --- API ---

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
  public function createCards($card_specs, $card_sublocation = 'DECK')
  {
    // XXX: this isn't going to work when `card_sublocation_index` is NULL yet
    //
    // XXX: Also, we need to `$this->shuffle()` the new cards
    // (since all of them are created with a card_order of -1).
    // We should move that into this function!

    if (count($card_specs) == 0) {
      throw new \BgaVisibleSystemException('Cannot `createCards()` with empty list of $card_specs!');
    }

    $values = [];
    foreach ($card_specs as $card_spec) {
      // XXX: update some of these values
      $values[] =
        '("'.$card_spec['card_type_group'].'", "' . $card_spec['card_type'] . '", "'.$this->location_.'", "DECK", NULL, -1' . ')';
    }

    shuffle($values);
    $sql =
      'INSERT INTO card (`card_type_group`, `card_type`, `card_location`, `card_sublocation`, `card_sublocation_index`, `card_order`) VALUES ' .
      implode(',', $values);
    $this->DbQuery($sql);
  }

  // Takes cards from all $card_sublocations and moves them to
  // $destination_sublocation.
  public function moveAll($card_sublocations, ?int $sublocation_index = SUBLOCATION_INDEX_ANY, $destination_sublocation = 'DECK'): void
  {
    foreach ($this->rawGetAll($card_sublocations, $sublocation_index) as $card) {
      $this->placeOnTop($card, $destination_sublocation);
    }
  }

  // // XXX: Instead, create a WcDeck with the intended
  // // location & sublocation_index and use `placeOn{Top,Bottom}()`
  //
  // function move($card_id, $destination_location, $destination_sublocation, $destination_sublocation_index = null) {
  //     $update_subexprs = [
  //         'card_location = "'.$destination_location.'"',
  //         'card_sublocation = "'.$destination_sublocation.'"',
  //     ];
  //     if (!is_null($destination_sublocation_index)) {
  //         $update_subexprs[] = 'card_sublocation_index = '.$destination_sublocation_index;
  //     }
  //     $this->DbQuery('UPDATE `card` SET ' . implode(',', $update_subexprs) . ' WHERE `id` = ' . $card_id);
  // }

  private function rawGetAll($card_sublocations = ['DECK'], ?int $sublocation_index = null)
  {
    return $this->getCollectionFromDB('SELECT * FROM `card` WHERE ' . $this->buildWhereClause($card_sublocations, $sublocation_index));
  }

  // XXX: Should this return things in *every* sublocation-index, rather than in the NULL sublocation-index?
  public function getAll($card_sublocations = ['DECK'], ?int $sublocation_index = SUBLOCATION_INDEX_ANY)
  {
    return array_map(function ($row) {
      return $this->CardT_::fromRow($row);
    }, $this->rawGetAll($card_sublocations, $sublocation_index));
  }

  /** @return CardT|null */
  public function get(int $card_id)
  {
    return $this->CardT_::fromRow($this->CardT_, $this->rawGet($card_id));
  }

  // Same as `get()`, but throws an exception if the card is not found.
  /** @return CardT */
  public function mustGet(int $card_id)
  {
    $card = $this->get($card_id);
    if ($card === null) {
      throw new \BgaVisibleSystemException('Unable to find card with ID ' . $card_id);
    }
    return $card;
  }

  /** @return string[]|null */
  private function rawGet(int $card_id)
  {
    // self::trace("WcDeck::rawGet(card_id={$card_id})");
    // XXX: this should probably return an error if the card is not within the scope of this WcDeck
    return $this->getObjectFromDB('SELECT * FROM `card` WHERE `id` = ' . $card_id);
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  private function rawGetAllOfType($card_type)
  {
    return $this->getCollectionFromDB('SELECT * FROM `card` WHERE `card_type` = "' . $card_type . '"');
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  private function rawGetAllOfTypeGroup(string $card_type_group)
  {
    return $this->getCollectionFromDB('SELECT * FROM `card` WHERE `card_type_group` = "' . $card_type_group . '"');
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  function getAllOfTypeGroup(string $card_type_group)
  {
    return array_map(function ($row) {
      return $this->CardT_::fromRow($this->CardT_, $row);
    }, $this->rawGetAllOfTypeGroup($card_type_group));
  }

  // Returns the top `Card` in the indicated $card_sublocation, or
  // `null` iff it is empty.
  private function rawPeekTop($card_sublocation = 'DECK', ?int $sublocation_index = null)
  {
    $sql =
      'SELECT * FROM `card` WHERE ' . $this->buildWhereClause([$card_sublocation], $sublocation_index) . ' ORDER BY card_order ASC LIMIT 1';
    self::trace("rawPeekTop(): {$sql}");
    return $this->getObjectFromDB($sql);
  }

  // Returns the top `Card` in the indicated $card_sublocation, or
  // `null` iff it is empty.
  /** @return CardT|null */
  public function peekTop($card_sublocation = 'DECK', ?int $sublocation_index = null)
  {
    return $this->CardT_::fromRow($this->CardT_, $this->rawPeekTop($card_sublocation, $sublocation_index));
  }

  // Returns the top `Card` in the indicated $card_sublocation, and
  // moves it to $destination_sublocation, where it is placed on top.
  //
  // If the deck is empty, if $auto_reshuffle is true and there are
  // cards in $destination_sublocation, `moveAll()` them back to
  // $card_sublocation and then `shuffle()` them and try again.  If
  // $auto_reshuffle is false, or if there are no cards in either
  // sublocation, returns `null`.
  //
  /** @return CardT|null */
  private function drawAndDiscard(
    $card_sublocation = 'DECK',
    ?int $sublocation_index = null,
    string $destination_sublocation = 'DISCARD',
    bool $auto_reshuffle = false
  )
  {
    $card = $this->CardT_::fromRow($this->CardT_, $this->rawPeekTop($card_sublocation, $sublocation_index));

    if ($card === null) {
      if (!$auto_reshuffle) {
        return null;
      }
      $this->moveAll([$destination_sublocation], $card_sublocation);
      $this->shuffle($card_sublocation, $sublocation_index);
      return $this->drawAndDiscard($card_sublocation, $sublocation_index, $destination_sublocation, /*auto_reshuffle=*/ false);
    }

    $this->placeOnTop($card, $destination_sublocation);
    // XXX: should $card reflect the before position or the after position?
    return $card;
  }

  // Like `drawAndDiscard()`, but repeats until $predicate returns
  // true for a card.  Cards that do not match are placed in
  // $destination_sublocation.
  private function rawDrawAndDiscardUntil(
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
  //
  // XXX: Need to wrap this with a non-raw function.
  private function rawDrawAndDiscardFirstMatching($predicate, string $card_sublocation = 'DECK', ?int $sublocation_index = null, string $destination_sublocation = 'DISCARD')
  {
    $cards = $this->rawGetAll([$card_sublocation], $sublocation_index);

    foreach ($cards as $card) {
      if ($predicate($card)) {
        $this->placeOnTop($card, $destination_sublocation, $sublocation_index);
        return $card;
      }
    }

    return null;
  }

  /** @param CardT $card */
  public function placeOnTop($card, string $sublocation, ?int $sublocation_index = NULL): void
  {
    $this->shiftCardOrder($sublocation, $sublocation_index, 1);
    $this->updateCard($card, $sublocation, /*card_order=*/ 0, $sublocation_index);
  }

  public function placeOnBottom(Card $card, string $card_sublocation, ?int $sublocation_index = NULL): void
  {
    $this->updateCard($card, $card_sublocation, /*card_order=*/ $this->readMaxCardOrder($card_sublocation, $sublocation_index) + 1, $sublocation_index);
  }

  // --- This should probably become internal, but it's temporarily external until the API is fleshed out ---

  // XXX: This is partially duplicated by `updateCard()` in "DataLayer.php" in Burgle Bros 2.
  /** @param CardT $card */
  public function updateCard($card, string $sublocation, int $card_order, ?int $sublocation_index)
  {
    $update_subexprs = [
      'card_location = "' . $this->location_ . '"',
      'card_sublocation = "' . $sublocation . '"',
      'card_order = ' . $card_order,
    ];
    if (!is_null($sublocation_index)) {
      $update_subexprs[] = 'card_sublocation_index = ' . $sublocation_index;
    } else {
      $update_subexprs[] = 'card_sublocation_index = NULL';
    }
    $this->DbQuery('UPDATE `card` SET ' . implode(',', $update_subexprs) . ' WHERE `id` = ' . $card->id());
  }

  // --- Internal helpers ---

  // Modifies all `card_order`s in $card_sublocation by $n.
  protected function shiftCardOrder(string $sublocation, ?int $sublocation_index, int $n): void
  {
    $this->DbQuery(
      'UPDATE `card` SET card_order=(card_order+' . $n . ') WHERE ' . $this->buildWhereClause([$sublocation], $sublocation_index)
    );
  }

  // Returns the number of cards in $card_sublocation.
  protected function cardCount(string $card_sublocation, ?int $sublocation_index): int
  {
    return $this->getUniqueValueFromDB(
      'SELECT COUNT(*) FROM `card` WHERE ' . $this->buildWhereClause([$card_sublocation], $sublocation_index)
    );
  }

  protected function readMaxCardOrder(string $card_sublocation, ?int $sublocation_index):int
  {
    return $this->getUniqueValueFromDB(
      'SELECT card_order FROM `card` WHERE ' .
        $this->buildWhereClause([$card_sublocation], $sublocation_index) .
        ' ORDER BY card_order DESC LIMIT 1'
    );
  }

  protected function readMinCardOrder(string $card_sublocation, ?int $sublocation_index):int
  {
    $sql =
      'SELECT card_order FROM `card` WHERE ' .
      $this->buildWhereClause([$card_sublocation], $sublocation_index) .
      ' ORDER BY card_order ASC LIMIT 1';
    self::trace("readMinCardOrder: {$sql}");
    return $this->getUniqueValueFromDB($sql);
  }

  // $card_sublocations is `string[]`.`
  protected function buildWhereClause($card_sublocations, ?int $sublocation_index): string
  {
    $clause = 'card_location = "' . $this->location_ . '"';

    if (!is_null($sublocation_index)) {
      $clause .= ' AND card_sublocation_index = ' . $sublocation_index;
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
