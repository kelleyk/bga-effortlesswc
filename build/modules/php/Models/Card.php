<?php declare(strict_types=1);

namespace EffortlessWC\Models;

class Card extends \WcLib\CardBase
{
  const CARD_TYPE_GROUP = 'main';

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
  public static function fromRow(string $CardT, $row)
  {
    return self::fromRowBase($CardT, $row);
  }
}
