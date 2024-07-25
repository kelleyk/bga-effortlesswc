<?php declare(strict_types=1);

namespace Effortless;

use Effortless\Models\ArmorCard;
use Effortless\Models\AttributeCard;
use Effortless\Models\ItemCard;
use Effortless\Models\Location;
use Effortless\Models\Seat;
use Effortless\Models\Setting;

class TableScore implements \JsonSerializable
{
  public $by_seat = [];

  public function jsonSerialize(): mixed
  {
    return [
      'bySeat' => $this->by_seat,
    ];
  }
}

// This score is "Greatness" from the rulebook.
class SeatScore implements \JsonSerializable
{
  public SeatAttributes $attribute_data;

  // Map from attribute abbreviation to score.
  public $attribute = [];
  // Map from set name to count.
  public $armor = [];
  // Map from card ID to score; score will be 0 if the seat does not have the $attributes to utilize the item.
  public $item = [];
  // Map from location ID (yes, location ID, not setting ID) to the score from the corresponding setting.
  public $setting = [];

  // Where the seat ranked against the others; 1 is first place.
  public int $place = 0;

  public function total(): int
  {
    $score = 0;

    foreach ($this->attribute as $attr => $points) {
      $score += $points;
    }
    foreach ($this->armor as $set_name => $points) {
      $score += $points;
    }
    foreach ($this->item as $card_id => $points) {
      $score += $points;
    }
    foreach ($this->setting as $location_id => $points) {
      $score += $points;
    }

    return $score;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'attributeData' => $this->attribute_data,
      'attribute' => $this->attribute,
      'armor' => $this->armor,
      'item' => $this->item,
      'setting' => $this->setting,
      'total' => $this->total(),
      'place' => $this->place,
    ];
  }
}

// This class describes the number of attribute cards and points of each type that a seat has in hand.  It's used for
// scoring, to determine if an item can be utilized, etc.
class SeatAttributes implements \JsonSerializable
{
  // Maps attribute abbreviation to number of cards.
  public $cards = [];
  // Maps attribute abbreviation to number of stat points.
  public $points = [];

  public function __construct()
  {
    foreach (ALL_ATTRIBUTES as $attribute) {
      $this->cards[$attribute] = 0;
      $this->points[$attribute] = 0;
    }
  }

  /**
    @param $cards Card[]
  */
  public static function fromCards($cards): SeatAttributes
  {
    $that = new SeatAttributes();

    foreach ($cards as $card) {
      if ($card instanceof AttributeCard) {
        $that->cards[$card->stat()] += 1;
        $that->points[$card->stat()] += $card->points();
      }
    }

    return $that;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'cards' => $this->cards,
      'points' => $this->points,
    ];
  }
}

function calculateScores(World $world): TableScore
{
  $table_score = new TableScore();

  // Calculate attribute totals for each seat.
  foreach (Seat::getAll($world) as $seat) {
    $seat_score = new SeatScore();
    $seat_score->attribute_data = SeatAttributes::fromCards($seat->hand($world));

    $table_score->by_seat[$seat->id()] = $seat_score;
  }

  // Calculate attribute scoring for each seat.
  foreach (ALL_ATTRIBUTES as $attribute) {
    $attr_values = array_map(function ($seat_score) use ($attribute) {
      return $seat_score->attribute_data->points[$attribute];
    }, $table_score->by_seat);

    foreach (calculateAttributeScores($world, $attr_values) as $seat_id => $points) {
      $table_score->by_seat[$seat_id]->attribute[$attribute] = $points;
    }
  }

  // Calculate armor scoring.
  foreach (Seat::getAll($world) as $seat) {
    $cards_by_set = [];

    foreach ($seat->hand($world) as $card) {
      if ($card instanceof ArmorCard) {
        $cards_by_set[$card->armorSet()] = ($cards_by_set[$card->armorSet()] ?? 0) + 1;
      }
    }

    foreach ($cards_by_set as $set_name => $card_qty) {
      $table_score->by_seat[$seat->id()]->armor[$set_name] = armorSetScore($card_qty);
    }
  }

  // Calculate item scoring.
  foreach (Seat::getAll($world) as $seat) {
    foreach ($seat->hand($world) as $card) {
      $seat_attrs = $table_score->by_seat[$seat->id()]->attribute_data;

      if ($card instanceof ItemCard) {
        $table_score->by_seat[$seat->id()]->item[$card->id()] = $card->usable($seat_attrs) ? $card->points() : 0;
      }
    }
  }

  // Calculate setting scoring.
  //
  // TODO: We could clean this up if there were an easier way to find setting/location pairs.
  $setting_by_pos = [];
  foreach (Setting::getAll($world) as $setting) {
    $setting_by_pos[$setting->sublocationIndex()] = $setting;
  }
  $location_by_pos = [];
  foreach (Location::getAll($world) as $location) {
    $location_by_pos[$location->sublocationIndex()] = $location;
  }
  for ($i = 0; $i < 6; ++$i) {
    $score_ctx = new ScoringContext();
    $setting_by_pos[$i]->onScoring($world, $score_ctx);
    $setting_scores = $score_ctx->scores();

    foreach (Seat::getAll($world) as $seat) {
      $score = $setting_scores[$seat->id()] ?? 0;
      $table_score->by_seat[$seat->id()]->setting[$location_by_pos[$i]->id()] = $score;
    }
  }

  return $table_score;
}

// $attr_values is {seat_id: attr_points}; returns {seat_id: points}.
//
// "The player with the most points in each attribute scores that many points.  In 4 and 5 player games, the second
// place player also scores.  If there's a tie for first, nobody scores for second."
/** @return int[] */
function calculateAttributeScores(World $world, $attr_values)
{
  // Sort highest-first.
  uasort($attr_values, function ($a, $b) {
    return $b - $a;
  });

  // Find the highest attribute score any seat has; only players with that atttribute value will receive points for it.
  $highest_value = max($attr_values);
  $scoring_values = [$highest_value];

  // ... unless nobody is tied and it's a 4 or 5 player game, in which case second place also scores.
  $tied_players = count(
    array_filter($attr_values, function ($attr_value) use ($highest_value) {
      return $attr_value >= $highest_value;
    })
  );
  if (count($attr_values) >= 4 && $tied_players == 1) {
    $scoring_values[] = max(
      array_filter($attr_values, function ($attr_value) use ($highest_value) {
        return $attr_value < $highest_value;
      })
    );
  }

  return array_map(function ($attr_value) use ($scoring_values) {
    return in_array($attr_value, $scoring_values) ? $attr_value : 0;
  }, $attr_values);
}

function armorSetScore(int $card_qty): int
{
  switch ($card_qty) {
    case 1:
      return 1;
    case 2:
      return 4;
    case 3:
      return 8;
    case 4:
      return 13;
    default:
      throw new \BgaVisibleSystemException('Unexpected number of cards in armor set: ' . $card_qty);
  }
}
