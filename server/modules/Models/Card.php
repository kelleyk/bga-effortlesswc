<?php declare(strict_types=1);

namespace EffortlessWC\Models;

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
    }

    return $card;
  }

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
}

class AttributeCard extends Card
{
  const CARD_TYPE = 'attr';
}

class ArmorCard extends Card
{
  const CARD_TYPE = 'armor';
}

class ItemCard extends Card
{
  const CARD_TYPE = 'item';
}

// XXX: We'll eventually need GritCard, ExperienceCard... others?
