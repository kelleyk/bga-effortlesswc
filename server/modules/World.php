<?php declare(strict_types=1);

namespace EffortlessWC;

require_once('module/php/WcLib/WcDeck.php');

use \WcLib\Card;

interface World
{
  // Map from seat ID to effort count.
  public function effortBySeat(Setting $setting);

  // Returns an array whose keys are the same as $effort_by_seat.  Values are in the range [1, 5], where key(s) with the
  // value 1 have, or are tied for, the largest values in $effort_by_seat, and so on.  If $invert == false, rank 1 will
  // be given to the lowest values instead.
  //
  // $outcome_good should be true iff the ranking is for something that players *want* (e.g. postive points), and false
  // iff it is for something that players do not want (e.g. negative points).  This is important when tie-breaking.
  //
  // TODO: This function will account for things like the Fighter's tie-breaking ability.
  public function rankByEffort($effort_by_seat, bool $outcome_good, bool $invert = false);

  // Like `rankByEffort()` but returns a list of the seat IDs at rank 1.
  public function topByEffort($effort_by_seat, bool $outcome_good, bool $invert = false);

  public function allEffortPiles();

  public function locations();

  public function activeSeat(): Seat;

  public function moveCardToLocation(Card $card, Location $loc);

  public function moveCardToHand(Card $card, Seat $seat);

  // This is roughly `moveCardToHand()` from the deck.
  public function drawCardToHand(Seat $seat);

  public function discardCard(Card $card);

  // Moves one effort from $src to $dst.  They may be piles or locations.
  public function moveEffort($src, $dst);

  public function fillCards(Location $loc): void;

  public function nextState(string $transition): void;
}
