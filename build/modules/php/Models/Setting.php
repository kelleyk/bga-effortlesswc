<?php declare(strict_types=1);

abstract class Setting
{
}

class ActiveSetting extends Setting
{
  const SETTING_ID = 'setting:active';
  const SET_ID = SET_BASE;

  // >=1 effort -> 3 pt
}

class BarrenSetting extends Setting
{
  const SETTING_ID = 'setting:barren';
  const SET_ID = SET_BASE;

  // no effects
}

class BattlingSetting extends Setting
{
  const SETTING_ID = 'setting:battling';
  const SET_ID = SET_BASE;

  // most effort -> 8 pt
}

// "When fighting a Threat, gain +1 Grit for every 3 Effort you have here."
class CapableSetting extends Setting
{
  const SETTING_ID = 'setting:capable';
  const SET_ID = SET_HUNTED;
}

// XXX: This is "Lost" from the rulebook.
//
// "When fighting a Threat at this Location, players may only use 2 of the Threat's Weaknesses to deal damage.
// (Critical Weaknesses still do 3 damage.)"
class CorruptedSetting extends Setting
{
  const SETTING_ID = 'setting:corrupted';
  const SET_ID = SET_HUNTED;
}

class CrowdedSetting extends Setting
{
  const SETTING_ID = 'setting:crowded';
  const SET_ID = SET_BASE;
}

// Least effort => 3 points.
class EerieSetting extends Setting
{
  const SETTING_ID = 'setting:eerie';
  const SET_ID = SET_BASE;
}

// "When scoring Items, gain 1 experience for every 3 effort you have here."
class EquippedSetting extends Setting
{
  const SETTING_ID = 'setting:equipped';
  const SET_ID = SET_ALTERED;
}

// XXX: (?) The person with the most effort here gets -1 point for each 2 total effort here.
class GhostlySetting extends Setting
{
  const SETTING_ID = 'setting:ghostly';
  const SET_ID = SET_BASE;
}

// Least effort => -5 points.
class HiddenSetting extends Setting
{
  const SETTING_ID = 'setting:hidden';
  const SET_ID = SET_BASE;
}

// Most effort => 1 point for every 2 total effort here.
class HolySetting extends Setting
{
  const SETTING_ID = 'setting:holy';
  const SET_ID = SET_BASE;
}

// 1 point per effort.
class LivelySetting extends Setting
{
  const SETTING_ID = 'setting:lively';
  const SET_ID = SET_BASE;
}

// "When scoring attributes, gain +1 to all attributes for every 4 effort you have here."
class MagicalSetting extends Setting
{
  const SETTING_ID = 'setting:magical';
  const SET_ID = SET_ALTERED;
}

// class NonexistentSetting - KS exclusive

// "Threats are dealt facedown at this Location.  Reveal the threat when fighting."
class OvergrownSetting extends Setting
{
  const SETTING_ID = 'setting:overgrown';
  const SET_ID = SET_HUNTED;
}

// 3 points for every 2 effort.
class PeacefulSetting extends Setting
{
  const SETTING_ID = 'setting:peaceful';
  const SET_ID = SET_BASE;
}

// Most effort => -3 points.
class QuietSetting extends Setting
{
  const SETTING_ID = 'setting:quiet';
  const SET_ID = SET_BASE;
}

// "When scoring armor, gain 1 wild armor piece for every 3 effort you have here.  (You cannot score more than 13 points
// for one set of armor.)"
class ShelteredSetting extends Setting
{
  const SETTING_ID = 'setting:sheltered';
  const SET_ID = SET_ALTERED;
}

// "At the end of the game, before scoring, draw 1 card from the top of the deck for every 3 effort you have here."
class SecretSetting extends Setting
{
  const SETTING_ID = 'setting:secret';
  const SET_ID = SET_ALTERED;
}

// "Threats defeated at this Location score double Greatness."
class StarvedSetting extends Setting
{
  const SETTING_ID = 'setting:starved';
  const SET_ID = SET_HUNTED;
}

// "When scoring attributes, the player with the most Effort here gains +2 to all attributes."
class TranscendentSetting extends Setting
{
  const SETTING_ID = 'setting:transcendent';
  const SET_ID = SET_ALTERED;
}

// "At the end of the game, before Scoring Locations, players may move effort from here to any other locations (in
// reverse turn order)."
class TravelingSetting extends Setting
{
  const SETTING_ID = 'setting:traveling';
  const SET_ID = SET_HUNTED;
}

// -1 point per effort.
class TreacherousSetting extends Setting
{
  const SETTING_ID = 'setting:treacherous';
  const SET_ID = SET_BASE;
}
