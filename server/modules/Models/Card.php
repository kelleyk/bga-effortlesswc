<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\World;

abstract class Card extends \WcLib\CardBase
{
  const CARD_TYPE_GROUP = 'main';

  protected bool $face_down_;

  public static function getById(World $world, int $id): ?Card
  {
    return $world->table()->mainDeck->get($id);
  }

  public static function mustGetById(World $world, int $id): Card
  {
    $card = self::getById($world, $id);
    if ($card === null) {
      throw new \WcLib\Exception('Could not find card with id=' . $id);
    }
    return $card;
  }

  // This is meant to be overridden by subclasses; but subclasses sometimes need to change its signature, which is why
  // it's not on `CardBase`.
  //
  // N.B.: This is a `string[]` because MySQL returns every column as a string, regardless of the column's actual type.
  // (XXX: Is this true for NULL values as well?)
  //
  // XXX: We really just want to say "this must return an instance of `get_called_class()` or null"; it should be
  // possible to do that without the template parameter.
  /**
    @param string[]|null $row
    @return Location|null
  */
  public static function fromRow(string $CardT, $deck, $row)
  {
    $card = self::fromRowBase($CardT, $deck, $row, function ($card_type) {
      return explode('_', $card_type)[0];
    });

    if ($row !== null && $card !== null) {
      $card->face_down_ = $row['card_face_down'] == '1';
      $card->init();
    }

    return $card;
  }

  abstract protected function init(): void;

  public function setFaceDown(bool $face_down): void
  {
    $this->face_down_ = $face_down;
    $this->updateCard([
      'card_face_down' => $face_down,
    ]);
  }

  public function isFaceDown(): bool
  {
    return $this->face_down_;
  }

  // If $force_visible, the card's details will be rendered even if it is face-down.  Otherwise, they will be rendered
  // only if it's face-up.  This is useful when, for example, a seat must choose between two face-down cards (that they
  // are allowed to look at while making the choice).
  public function renderForClient(World $world, bool $force_visible = false): array
  {
    $visible = $force_visible || !$this->isFaceDown();
    return array_merge(parent::renderForClientBase($visible), [
      'faceDown' => $this->isFaceDown(),
      'visible' => $visible,
    ]);
  }

  public function renderForNotif(World $world): string
  {
    return 'Card[' . $this->id() . ']';
  }
}

class AttributeCard extends Card
{
  // The actual types stored in card rows are of the form "attr_<stat>_<points>" (e.g. "attr_str_2").
  const CARD_TYPE = 'attr';

  protected string $stat_;
  protected int $points_;

  protected function init(): void
  {
    $parts = explode('_', $this->type());
    if (count($parts) != 3) {
      throw new \BgaVisibleSystemException('Unexpected card-type for attribute card.');
    }

    $this->stat_ = $parts[1];
    $this->points_ = intval($parts[2]);
  }

  public function renderForClient(World $world, bool $force_visible = false): array
  {
    $result = parent::renderForClient($world, $force_visible);

    if ($force_visible || !$this->isFaceDown()) {
      $result = array_merge($result, [
        'cardTypeStem' => 'attr',
        'stat' => $this->stat_,
        'points' => $this->points_,
      ]);
    }

    return $result;
  }

  public function stat(): string
  {
    return $this->stat_;
  }

  public function points(): int
  {
    return $this->points_;
  }
}

class ArmorCard extends Card
{
  // The actual types stored in card rows are of the form "armor_<set>_<piece>" (e.g. "armor_mage_feet").
  const CARD_TYPE = 'armor';

  protected string $armor_set_;
  protected string $armor_piece_;

  protected function init(): void
  {
    $parts = explode('_', $this->type());
    if (count($parts) != 3) {
      throw new \BgaVisibleSystemException('Unexpected card-type for armor card.');
    }

    $this->armor_set_ = $parts[1];
    $this->armor_piece_ = $parts[2];
  }

  public function renderForClient(World $world, bool $force_visible = false): array
  {
    $result = parent::renderForClient($world, $force_visible);

    if ($force_visible || !$this->isFaceDown()) {
      $result = array_merge($result, [
        'cardTypeStem' => 'armor',
        'armorSet' => $this->armor_set_,
        'armorPiece' => $this->armor_piece_,
      ]);
    }

    return $result;
  }

  public function armorSet(): string
  {
    return $this->armor_set_;
  }

  public function armorPiece(): string
  {
    return $this->armor_piece_;
  }
}

class ItemCard extends Card
{
  // The actual types stored in card rows are of the form "item_<item_no>" (e.g. "item_7").
  const CARD_TYPE = 'item';

  protected int $item_no_;

  protected function init(): void
  {
    $parts = explode('_', $this->type());
    if (count($parts) != 2) {
      throw new \BgaVisibleSystemException('Unexpected card-type for item card.');
    }

    $this->item_no_ = intval($parts[1]);
  }

  public function renderForClient(World $world, bool $force_visible = false): array
  {
    $result = parent::renderForClient($world, $force_visible);

    if ($force_visible || !$this->isFaceDown()) {
      $result = array_merge($result, [
        'cardTypeStem' => 'attr',
        'itemNo' => $this->item_no_,
      ]);
    }

    return $result;
  }
}

// XXX: We'll eventually need GritCard, ExperienceCard... others?
