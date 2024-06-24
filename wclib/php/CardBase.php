<?php declare(strict_types=1);

namespace WcLib;

abstract class CardBase
{
  protected int $id_;
  protected string $type_group_;
  protected string $type_;
  protected string $location_;
  protected string $sublocation_;
  protected int $sublocation_index_;
  protected int $order_;

  // N.B.: This returns an unpopulated instance of the appropriate class.
  //
  // XXX: Can we eventually pull all of this logic into CardBase, and use a (CARD_TYPE_GROUP, CARD_TYPE) pair, with
  // CARD_TYPE_GROUP defined on e.g. Location?
  //
  // N.B.: We are using a doc-block annotation here rather than a native PHP type annotation because those don't support
  // union types before PHP 8.0.  Once BGA moves to PHP 8.2, we can change this.
  //
  /** @param string|int $card_type */
  protected static function newInstByType($card_type)
  {
    if ($card_type == '') {
      throw new \feException('Empty card type!');
    }

    // Should be e.g. `Location`---one of the "leaf base classes" that defines CARD_TYPE_GROUP.
    $called_base_class = get_called_class();

    // XXX: We should only need to do this once, and then we
    // should be able to cache it.
    $classById = [];
    foreach (get_declared_classes() as $class) {
      if (is_subclass_of($class, self::class)) {
        $rc = new \ReflectionClass($class);
        if ($class::CARD_TYPE_GROUP == $called_base_class::CARD_TYPE_GROUP) {
          if (!$rc->isAbstract()) {
            $classById[$class::CARD_TYPE] = $rc;
          }
        }
      }
    }

    return $classById[$card_type]->newInstance();
  }

  // XXX: What about the examples of `fromRow()` functions that require a `World` reference?
  //
  // XXX: We really just want to say "this must return an instance of `get_called_class()` or null"; it should be
  // possible to do that without the template parameter.
  /**
    @template CardT of CardBase
    @param class-string<CardT> $CardT;
    @param string[]|null $row
    @return CardT|null
  */
  public static function fromRowBase(string $CardT, $row)
  {
    if ($row === null) {
      return null;
    }

    $card = self::newInstByType($row['card_type']);
    $card->id_ = intval($row['id']);
    $card->type_group_ = $row['card_type_group'];
    $card->type_ = $row['card_type'];
    $card->location_ = $row['card_location'];
    $card->sublocation_ = $row['card_sublocation'];
    $card->sublocation_index_ = intval($row['card_sublocation_index']);
    $card->order_ = intval($row['card_order']);

    return $card;
  }

  public function id(): int {
    return $this->id_;
  }

  public function typeGroup(): string {
    return $this->type_group_;
  }

  public function type(): string {
    return $this->type_;
  }

  public function location(): string {
    return $this->location_;
  }

  public function sublocation(): string {
    return $this->sublocation_;
  }

  public function sublocationIndex(): int {
    return $this->sublocation_index_;
  }

  public function order(): int {
    return $this->order_;
  }
}
