<?php declare(strict_types=1);

namespace Effortless\States;

trait PostScoring
{
  use \Effortless\BaseTableTrait;

  public function stPostScoring()
  {
    $world = $this->world();

    $world->nextState(T_DONE);
  }

  public function argPostScoring()
  {
    // // XXX: Return all scoring info so that the client can draw the end-game screen.

    // foreach (Seat::getAll($this->world()) as $seat) {
    //   $scoring_details = new PlayerScoringData();

    //   $items = [];

    //   foreach ($seat->cards($world) as $card) {
    //     if ($card instanceof AttributeCard) {
    //       // XXX:
    //     } elseif ($card instanceof ArmorCard) {
    //       // XXX:
    //     } elseif ($card instanceof ItemCard) {
    //       // XXX:
    //     } else {
    //       throw new \BgaVisibleSystemException('Unexpected card type during scoring.');
    //     }
    //   }
    // }

    // // Armor (1/4/8/13 per set)

    // // Items (points as printed on the item, if we have the attributes to utilize it)

    // // Attributes
    // //

    // // Settings (top to bottom)

    // // Tie breakers:
    // //
    // // - Highest single attribute score
    // // - Highest single scored item
    // // - Most complete armor sets

    return array_merge($this->renderBoardState(), [
      'scoring' => \Effortless\calculateScores($this->world()),
    ]);
  }
}
