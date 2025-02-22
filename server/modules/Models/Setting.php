<?php declare(strict_types=1);

namespace Effortless\Models;

require_once realpath(__DIR__ . '/../StaticDataSetlocs.php');

use Effortless\ScoringContext;
use Effortless\World;

// XXX: We need a way to do sorting that lets the Fighter attribute do its tiebreaking thing.

abstract class Setting extends \WcLib\CardBase
{
  const CARD_TYPE_GROUP = 'setting';

  // Concrete subclasses must define:
  // - CARD_TYPE
  // - SET_ID
  // - OUTCOME_GOOD

  public static function getById(World $world, int $id): ?Setting
  {
    return $world->table()->settingDeck->get($id);
  }

  public static function getByPos(World $world, int $pos): ?Setting
  {
    return $world->table()->settingDeck->getByLocation('setloc', $pos);
  }

  public static function mustGetById(World $world, int $id): Setting
  {
    $setting = self::getById($world, $id);
    if ($setting === null) {
      throw new \WcLib\Exception('Could not find setting with id=' . $id);
    }
    return $setting;
  }

  /**
    @return Setting[]
  */
  public static function getAll(World $world)
  {
    return $world->table()->settingDeck->getAll(['SETLOC']);
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
    return self::fromRowBase($CardT, $deck, $row);
  }

  public function renderForClient(World $world): array
  {
    return array_merge(parent::renderForClientBase(/*visible=*/ true), [
      'sortEffortInverted' => false, // XXX: need actual values
    ]);
  }

  public function renderForNotif(World $world): string
  {
    return '<strong>' . $this->metadata()['name'] . '</strong>';
  }

  // Returns the location in the same position as this setting (that is, the one that is "puzzle pieced" together with
  // it).
  public function pairedLocation(World $world): Location
  {
    $set_to_loc = $world->table()->getSetToLocMap();
    return Location::mustGetById($world, $set_to_loc[$this->id()]);
  }

  /** @return EffortPile[] */
  public function effortPiles(World $world)
  {
    return $this->pairedLocation($world)->effortPiles($world);
  }

  // Returns an associative array mapping seat ID to number of effort at this setloc.
  /** @return int[] */
  public function effortBySeat(World $world)
  {
    $qty_by_seat = [];
    foreach ($this->effortPiles($world) as $pile) {
      $qty_by_seat[$pile->seatId()] = $pile->qty();
    }
    return $qty_by_seat;
  }

  // XXX: make abstract
  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    throw new \feException('XXX: needs to be implemented on each thing');
  }

  // Returns an array containing the IDs of the seat(s) that will be affected by this setting at scoring time.  This is
  // used to highlight certain seats' effort counts in the UI.
  /** @return int[] */
  public function affectedSeats(World $world)
  {
    throw new \feException('XXX: needs to be implemented on each thing');
  }

  public function rankByEffort(World $world, bool $invert = false)
  {
    // XXX: read $outcome_good from class
    return $world->rankByEffort($world->effortBySeat($this), $this->isOutcomeGood(), $invert);
  }

  // Like `rankByEffort()` but returns a list of the seat IDs at rank 1.
  /** @return int[] */
  public function topByEffort(World $world, bool $invert = false)
  {
    // XXX: read $outcome_good from class
    return $world->topByEffort($world->effortBySeat($this), $this->isOutcomeGood(), $invert);
  }

  public function isOutcomeGood(): bool
  {
    $rc = new \ReflectionClass(get_called_class());
    if (!$rc->hasConstant('OUTCOME_GOOD')) {
      throw new \BgaVisibleSystemException(
        'Class ' . get_called_class() . ' does not define required constant OUTCOME_GOOD.'
      );
    }
    // XXX: We've changed this function; do we still need this suppression?
    /** @phan-suppress-next-line PhanUndeclaredConstantOfClass */
    return get_called_class()::OUTCOME_GOOD;
  }

  // Returns the total amount of effort at this location.
  public function totalEffort(World $world): int
  {
    return array_sum(
      array_map(function ($pile) {
        return $pile->qty();
      }, $this->effortPiles($world))
    );
  }

  // Returns a list of seat IDs that have at least $n effort here.
  /** @return int[] */
  public function seatsWithEffort(World $world, int $n)
  {
    $seats = [];
    foreach ($this->effortBySeat($world) as $seatId => $effort) {
      if ($effort >= $n) {
        $seats[] = $seatId;
      }
    }
    return $seats;
  }

  protected function metadata()
  {
    $type = explode(':', $this->type())[1];
    return SETTING_METADATA[$type];
  }

  public function locationArg(): int
  {
    return $this->sublocationIndex();
  }
}

// >=1 effort -> 3 pt
class ActiveSetting extends Setting
{
  const CARD_TYPE = 'setting:active';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->seatsWithEffort($world, 1);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, 3);
    }
  }
}

class BarrenSetting extends Setting
{
  const CARD_TYPE = 'setting:barren';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return [];
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    // No effects.
  }
}

// most effort -> 8 pt
class BattlingSetting extends Setting
{
  const CARD_TYPE = 'setting:battling';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    // $top = $this->topByEffort($world);
    // print('*** affectedSeats() for battling: ' . print_r($top, true) . "\n");
    // return $top;

    return $this->topByEffort($world);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, 8);
    }
  }
}

// -1 point per effort.
class TreacherousSetting extends Setting
{
  const CARD_TYPE = 'setting:treacherous';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = false;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->seatsWithEffort($world, 1);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->effortBySeat($world) as $seatId => $effort) {
      $score_ctx->givePoints($seatId, -1 * $effort);
    }
  }
}

// Each player with 5 or more effort gets 10 points.
class CrowdedSetting extends Setting
{
  const CARD_TYPE = 'setting:crowded';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->seatsWithEffort($world, 5);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, 10);
    }
  }
}

// Least effort => 3 points.
class EerieSetting extends Setting
{
  const CARD_TYPE = 'setting:eerie';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->topByEffort($world, /*invert=*/ true);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    $affected_seats = $this->affectedSeats($world);
    // $world->table()->notifyAllPlayers('XXX_debug', 'XXX: EERIE affected seats: ' . implode(',', $affected_seats), []);

    foreach ($affected_seats as $seatId) {
      $score_ctx->givePoints($seatId, 3);
    }
  }
}

// The person with the most effort here gets -1 point for each effort they have here.
class GhostlySetting extends Setting
{
  const CARD_TYPE = 'setting:ghostly';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = false;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->topByEffort($world);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    $effort_by_seat = $this->effortBySeat($world);
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, -1 * $effort_by_seat[$seatId]);
    }
  }
}

// Least effort => -5 points.
class HiddenSetting extends Setting
{
  const CARD_TYPE = 'setting:hidden';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = false;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->topByEffort($world, /*invert=*/ true);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, -5);
    }
  }
}

// Most effort => 1 point for every effort they have here.
class HolySetting extends Setting
{
  const CARD_TYPE = 'setting:holy';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->topByEffort($world);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    $effort_by_seat = $this->effortBySeat($world);
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, $effort_by_seat[$seatId]);
    }
  }
}

// 1 point per effort.
class LivelySetting extends Setting
{
  const CARD_TYPE = 'setting:lively';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->seatsWithEffort($world, 1);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->effortBySeat($world) as $seatId => $effort) {
      $score_ctx->givePoints($seatId, $effort);
    }
  }
}

// 3 points for every 2 effort.
class PeacefulSetting extends Setting
{
  const CARD_TYPE = 'setting:peaceful';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = true;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->seatsWithEffort($world, 2);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->effortBySeat($world) as $seatId => $effort) {
      $score_ctx->givePoints($seatId, 3 * intdiv($effort, 2));
    }
  }
}

// Most effort => -3 points.
class QuietSetting extends Setting
{
  const CARD_TYPE = 'setting:quiet';
  const SET_ID = SET_BASE;

  const OUTCOME_GOOD = false;

  /** @return int[] */
  public function affectedSeats(World $world)
  {
    return $this->topByEffort($world);
  }

  public function onScoring(World $world, ScoringContext $score_ctx): void
  {
    foreach ($this->affectedSeats($world) as $seatId) {
      $score_ctx->givePoints($seatId, -3);
    }
  }
}

// "When scoring attributes, the player with the most Effort here gains +2 to all attributes."
//
// DESIGNER: This only applies during the attribute scoring phase; the other attribute-related things in the scoring
// phase care about number of attribute cards, anyhow.
class TranscendentSetting extends Setting
{
  const CARD_TYPE = 'setting:transcendent';
  const SET_ID = SET_ALTERED;

  public function onScoringAttributes(World $world): void
  {
    // XXX: getParameter ...
  }
}

// "When scoring Items, gain 1 experience for every 3 effort you have here."
class EquippedSetting extends Setting
{
  const CARD_TYPE = 'setting:equipped';
  const SET_ID = SET_ALTERED;
}

// "When scoring attributes, gain +1 to all attributes for every 4 effort you have here."
class MagicalSetting extends Setting
{
  const CARD_TYPE = 'setting:magical';
  const SET_ID = SET_ALTERED;
}

// "When scoring armor, gain 1 wild armor piece for every 3 effort you have here.  (You cannot score more than 13 points
// for one set of armor.)"
class ShelteredSetting extends Setting
{
  const CARD_TYPE = 'setting:sheltered';
  const SET_ID = SET_ALTERED;
}

// "At the end of the game, before scoring, draw 1 card from the top of the deck for every 3 effort you have here."
class SecretSetting extends Setting
{
  const CARD_TYPE = 'setting:secret';
  const SET_ID = SET_ALTERED;
}

// "Threats defeated at this Location score double Greatness."
class StarvedSetting extends Setting
{
  const CARD_TYPE = 'setting:starved';
  const SET_ID = SET_HUNTED;
}

// "Threats are dealt facedown at this Location.  Reveal the threat when fighting."
//
// XXX: complication
class OvergrownSetting extends Setting
{
  const CARD_TYPE = 'setting:overgrown';
  const SET_ID = SET_HUNTED;
}

// "At the end of the game, before Scoring Locations, players may move effort from here to any other locations (in
// reverse turn order)."
//
// XXX: complication -- pre-scoring user input
class TravelingSetting extends Setting
{
  const CARD_TYPE = 'setting:traveling';
  const SET_ID = SET_HUNTED;

  public function onPreScoring(World $world): void
  {
    // XXX: getParameter ...
  }
}

// "When fighting a Threat, gain +1 Grit for every 3 Effort you have here."
//
// XXX: complication
class CapableSetting extends Setting
{
  const CARD_TYPE = 'setting:capable';
  const SET_ID = SET_HUNTED;
}

// "When fighting a Threat at this Location, players may only use 2 of the Threat's Weaknesses to deal damage.
// (Critical Weaknesses still do 3 damage.)"
//
// XXX: complication
//
// DESIGNER: "Lost" was removed from the expansion in favor of "Corrupted".
class CorruptedSetting extends Setting
{
  const CARD_TYPE = 'setting:corrupted';
  const SET_ID = SET_HUNTED;
}
