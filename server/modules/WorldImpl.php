<?php declare(strict_types=1);

namespace EffortlessWC;

class WorldImpl implements World
{
  private $table_;

  function __construct($table)
  {
    $this->table_ = $table;
  }

  public function effortBySeat(Setting $setting)
  {
    throw new \feException('XXX:');
  }

  public function fillCards(Location $loc): void
  {
    $cards_face_up_qty = 0;
    $cards_face_down_qty = 0;
    foreach ($loc->cards($world) as $card) {
      if ($card->isFaceDown()) {
        ++$cards_face_down_qty;
      } else {
        ++$cards_face_up_qty;
      }
    }

    if ($cards_face_up_qty > $loc->cardsFaceUp()) {
      throw new \BgaVisibleSystemException('Too many face-up cards in location.');
    }
    if ($cards_face_down_qty > $loc->cardsFaceUp()) {
      throw new \BgaVisibleSystemException('Too many face-down cards in location.');
    }

    for ($i = $cards_face_up_qty; $i < $loc->cardsFaceUp(); ++$i) {
      $this->mainDeck->drawTo('setloc', $loc->locationArg(), /*face_down=*/ false);
    }
    for ($i = $cards_face_down_qty; $i < $loc->cardsFaceDown(); ++$i) {
      $this->mainDeck->drawTo('setloc', $loc->locationArg(), /*face_down=*/ true);
    }
  }
}
