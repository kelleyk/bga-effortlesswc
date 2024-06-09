<?php

namespace WcLib;

// XXX: There's a lot of overlap between this and `CardPeer`.
//
// XXX: Make this `CardBase` instead?
class Card
{
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
}

class WcDeck
{
  // N.B.: For `card_order`, lower numbers are "first" (closer to
  // the top of a deck).

  // See `card` table schema for details.
  protected $dbo_;
  protected string $location_;


  function __construct($dbo, string $location)
  {
    $this->dbo_ = $dbo;
    $this->location_ = $location;
  }

  private function trace(string $msg): void {
    // XXX: refactoring dust
  }

  // --- New (WcLib) API ---

  // XXX: Does not handle auto-reshuffling.
  public function drawTo(string $dest_sublocation, ?int $dest_sublocation_index, string $src_sublocation = 'DECK', ?int $src_sublocation_index = NULL): Card
  {
    $card = $this->peekTop($src_sublocation, $src_sublocation_index);
    if (is_null($card)) {
      throw new \BgaVisibleSystemException('No cards in ' . $this->location_ . ',' . $src_sublocation . ',' . (is_null($src_sublocation_index) ? 'NULL': $src_sublocation_index));
    }
    $this->placeOnTop($card, $dest_sublocation, $dest_sublocation_index);
    return $this->get($card->id());
  }

  public function location(): string
  {
    return $this->location_;
  }

  // Randomly assign a new `card_order` value to each card in
  // $card_sublocation.
  public function shuffle($card_sublocation = 'DECK', ?int $sublocation_index = NULL)
  {
    self::trace('WcDeck: shuffle()');
    $cards = $this->rawGetAll([$card_sublocation], $sublocation_index);
    shuffle($cards);

    $i = 0; // XXX: Should be able to replace this with `foreach()` syntax.
    foreach ($cards as $card) {
      self::trace('WcDeck: shuffle(): setting card_order=' . $i . ' for id=' . $card['id']);
      $this->dbo_->DbQuery('UPDATE `card` SET card_order=' . $i . ' WHERE `id` = ' . $card['id']);
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
    $this->dbo_->DbQuery($sql);
  }

  // Takes cards from all $card_sublocations and moves them to
  // $destination_sublocation.
  public function moveAll($card_sublocations, ?int $sublocation_index, $destination_sublocation = 'DECK'): void
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
  //     $this->dbo_->DbQuery('UPDATE `card` SET ' . implode(',', $update_subexprs) . ' WHERE `id` = ' . $card_id);
  // }

  private function rawGetAll($card_sublocations = ['DECK'], ?int $sublocation_index)
  {
    return $this->dbo_->getCollectionFromDB('SELECT * FROM `card` WHERE ' . $this->buildWhereClause($card_sublocations, $sublocation_index));
  }

  // XXX: Should this return things in *every* sublocation-index, rather than in the NULL sublocation-index?
  public function getAll($card_sublocations = ['DECK'], ?int $sublocation_index = NULL)
  {
    return array_map(function ($row) {
      return Card::fromRow($row);
    }, $this->rawGetAll($card_sublocations, $sublocation_index));
  }

  // XXX: Or should this be on `Card`?
  public function get(int $card_id): Card
  {
    return Card::fromRow($this->rawGet($card_id));
  }

  private function rawGet($cardId)
  {
    self::trace("WcDeck::rawGet(cardId={$cardId})");
    // XXX: this should probably return an error if the card is not within the scope of this WcDeck
    $card = $this->dbo_->getObjectFromDB('SELECT * FROM `card` WHERE `id` = ' . $cardId);
    if (is_null($card['id'])) {
      throw new \BgaUserException(
        "WcDeck::rawGet(cardId={$cardId}) -- card ID is null; $card=" . print_r($card, true)
      );
    }
    return $card;
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  private function rawGetAllOfType($card_type)
  {
    return $this->dbo_->getCollectionFromDB('SELECT * FROM `card` WHERE `card_type` = "' . $card_type . '"');
  }

  // XXX: This should be `static` once `getCollectionFromDB()` is.
  private function rawGetAllOfTypeGroup(string $card_type_group)
  {
    return $this->dbo_->getCollectionFromDB('SELECT * FROM `card` WHERE `card_type_group` = "' . $card_type_group . '"');
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
  private function rawPeekTop($card_sublocation = 'DECK', ?int $sublocation_index)
  {
    $sql =
      'SELECT * FROM `card` WHERE ' . $this->buildWhereClause([$card_sublocation], $sublocation_index) . ' ORDER BY card_order ASC LIMIT 1';
    echo '*** rawPeekTop() query: ' . $sql . '<br />';
    self::trace("rawPeekTop(): {$sql}");
    return $this->dbo_->getObjectFromDB($sql);
  }

  // Returns the top `Card` in the indicated $card_sublocation, or
  // `null` iff it is empty.
  public function peekTop($card_sublocation = 'DECK', ?int $sublocation_index): ?Card
  {
    return Card::fromRow($this->rawPeekTop($card_sublocation, $sublocation_index));
  }

  // Returns the top `Card` in the indicated $card_sublocation, and
  // moves it to $destination_sublocation, where it is placed on top.
  //
  // If the deck is empty, if $auto_reshuffle is true and there are
  // cards in $destination_sublocation, `moveAll()` them back to
  // $card_sublocation and then `shuffle()` them and try again.  If
  // $auto_reshuffle is false, or if there are no cards in either
  // sublocation, returns `null`.
  private function rawDrawAndDiscard($card_sublocation = 'DECK', ?int $sublocation_index, string $destination_sublocation = 'DISCARD', bool $auto_reshuffle = false): ?Card
  {
    $card = $this->rawPeekTop($card_sublocation, $sublocation_index);

    if ($card === null) {
      if (!$auto_reshuffle) {
        return null;
      }
      $this->moveAll([$destination_sublocation], $card_sublocation);
      $this->shuffle($card_sublocation, $sublocation_index);
      return $this->rawDrawAndDiscard($card_sublocation, $sublocation_index, $destination_sublocation, /*auto_reshuffle=*/ false);
    }

    $this->placeOnTop($card, $destination_sublocation);
    // XXX: should $card reflect the before position or the after position?
    return $card;
  }

  public function drawAndDiscard(
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
  private function rawDrawAndDiscardFirstMatching($predicate, string $card_sublocation = 'DECK', ?int $sublocation_index, string $destination_sublocation = 'DISCARD')
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

  public function placeOnTop(Card $card, string $sublocation, ?int $sublocation_index = NULL): void
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
  public function updateCard(Card $card, string $sublocation, int $card_order, ?int $sublocation_index)
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
    $this->dbo_->DbQuery('UPDATE `card` SET ' . implode(',', $update_subexprs) . ' WHERE `id` = ' . $card->id());
  }

  // --- Internal helpers ---

  // Modifies all `card_order`s in $card_sublocation by $n.
  protected function shiftCardOrder(string $sublocation, ?int $sublocation_index, int $n): void
  {
    $this->dbo_->DbQuery(
      'UPDATE `card` SET card_order=(card_order+' . $n . ') WHERE ' . $this->buildWhereClause([$sublocation], $sublocation_index)
    );
  }

  // Returns the number of cards in $card_sublocation.
  protected function cardCount(string $card_sublocation, ?int $sublocation_index): int
  {
    return $this->dbo_->getUniqueValueFromDB(
      'SELECT COUNT(*) FROM `card` WHERE ' . $this->buildWhereClause([$card_sublocation], $sublocation_index)
    );
  }

  protected function readMaxCardOrder(string $card_sublocation, ?int $sublocation_index):int
  {
    return $this->dbo_->getUniqueValueFromDB(
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
    return $this->dbo_->getUniqueValueFromDB($sql);
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
