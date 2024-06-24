<?php declare(strict_types=1);

namespace EffortlessWC\Models;

use EffortlessWC\World;

abstract class Card extends \WcLib\CardBase
{
  const CARD_TYPE_GROUP = 'main';

  protected bool $face_down_;

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

  public function renderForClient(World $world): array
  {
    return array_merge(parent::renderForClientBase(!$this->isFaceDown()), [
      'faceDown' => $this->isFaceDown(),
    ]);
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

  public function renderForClient(World $world): array
  {
    $result = parent::renderForClient($world);

    if (!$this->isFaceDown()) {
      $result = array_merge($result, [
        'cardTypeStem' => 'attr',
        'stat' => $this->stat_,
        'points' => $this->points_,
      ]);
    }

    return $result;
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

  public function renderForClient(World $world): array
  {
    $result = parent::renderForClient($world);

    if (!$this->isFaceDown()) {
      $result = array_merge($result, [
        'cardTypeStem' => 'armor',
        'armorSet' => $this->armor_set_,
        'armorPiece' => $this->armor_piece_,
      ]);
    }

    return $result;
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

  public function renderForClient(World $world): array
  {
    $result = parent::renderForClient($world);

    if (!$this->isFaceDown()) {
      $result = array_merge($result, [
        'cardTypeStem' => 'attr',
        'itemNo' => $this->item_no_,
      ]);
    }

    return $result;
  }
}

// XXX: We'll eventually need GritCard, ExperienceCard... others?
