<?php declare(strict_types=1);

abstract class Location
{
}

// class AlleyLocation extends Location -- KS exclusive

// "Move any other effort from any other location to here."
//
// XXX: UI input -- pick setloc and effort type
class CabinLocation extends Location
{
  const LOCATION_ID = 'location:cabin';
  const SET_ID = SET_HUNTED;
}

// "Take both cards here and replace one with a card from your hand."
class CaravanLocation extends Location
{
  const LOCATION_ID = 'location:hunted';
  const SET_ID = SET_HUNTED;
}

class CaveLocation extends Location
{
  const LOCATION_ID = 'location:cave';
  const SET_ID = SET_BASE;
}

// "Move one of your effort from any other location to here."
//
// XXX: UI input -- pick setloc
class CityLocation extends Location
{
  const LOCATION_ID = 'location:city';
  const SET_ID = SET_BASE;
}

// "Take 1 card from here and discard the other."
//
// XXX: UI input -- pick card from options
class ColiseumLocation extends Location
{
  const LOCATION_ID = 'location:coliseum';
  const SET_ID = SET_BASE;
}

// "Take one of the top 2 cards from the discard.  (If there are less than 2 cards, add the top card from the deck.)"
//
// XXX: UI input -- pick card from options
class CryptLocation extends Location
{
  const LOCATION_ID = 'location:crypt';
  const SET_ID = SET_BASE;
}

// "Move one of your other effort from here to any other location."
//
// XXX: UI input -- pick setloc
class DocksLocation extends Location
{
  const LOCATION_ID = 'location:docks';
  const SET_ID = SET_BASE;
}

// "Move another player's effort from here to an adjacent location OR from an adjacent location to here."
//
// XXX: UI input -- pick setloc, effort
class DungeonLocation extends Location
{
  const LOCATION_ID = 'location:dungeon';
  const SET_ID = SET_ALTERED;
}

// "Move any other effort from here to any other location."
//
// XXX: UI input -- pick setloc
class ForestLocation extends Location
{
  const LOCATION_ID = 'location:forest';
  const SET_ID = SET_HUNTED;
}

// "Take the card and action of an adjacent location."
//
// XXX: UI input -- pick setloc
//
// XXX: complication -- then begin resolving effect of the other location
class GardenLocation extends Location
{
  const LOCATION_ID = 'location:garden';
  const SET_ID = SET_ALTERED;
}

// "If an attribute is filled on this Location, the threat here gains an additional weakness to cards of that
// attribute."
//
// XXX: complication -- effort here is in a particular sublocation
class LaboratoryLocation extends Location
{
  const LOCATION_ID = 'location:laboratory';
  const SET_ID = SET_HUNTED;
}

// "Effort cannot be moved to or from this location."
//
// XXX: complication -- this needs to affect all moves
class LabyrinthLocation extends Location
{
  const LOCATION_ID = 'location:labyrinth';
  const SET_ID = SET_HUNTED;
}

// class LairLocation extends Location -- exclusive Dragon mini-expansion

// "View both cards here and take 1.  (Replace the missing card face-down.)"
//
// XXX: UI input -- pick card from group
class LibraryLocation extends Location
{
  const LOCATION_ID = 'location:library';
  const SET_ID = SET_BASE;
}

// "Discard a card from your hand, then take both cards here."
//
/// XXX: UI input -- pick card from hand
class MarketLocation extends Location
{
  const LOCATION_ID = 'location:market';
  const SET_ID = SET_BASE;
}

// "Take 1 card from here, then flip the other."
//
// XXX: UI input -- pick card from group
class ObservatoryLocation extends Location
{
  const LOCATION_ID = 'location:observatory';
  const SET_ID = SET_ALTERED;
}

// "Swap any other effort here with an effort at any other location."
//
// XXX: UI input -- pick effort here AND pick setloc/effort
class PortalLocation extends Location
{
  const LOCATION_ID = 'location:portal';
  const SET_ID = SET_ALTERED;
}

// "Move another player's effort from any other location to here."
//
// XXX: UI input -- pick setloc/effort
class PrisonLocation extends Location
{
  const LOCATION_ID = 'location:prison';
  const SET_ID = SET_BASE;
}

// "Discard a card at another location."
//
// XXX: UI input -- pick card at setloc
class RiverLocation extends Location
{
  const LOCATION_ID = 'location:river';
  const SET_ID = SET_BASE;
}

// "Move one of your other effort from here to an adjacent location OR from an adjacent location to here."
//
// XXX: UI input -- pick setloc
class StablesLocation extends Location
{
  const LOCATION_ID = 'location:stables';
  const SET_ID = SET_ALTERED;
}

// class TavernLocation extends Location -- KS exclusive

// "Discard a card from your hand.  Then, take the top 2 cards from the deck."
//
// XXX: UI input -- select card from hand
class TempleLocation extends Location
{
  const LOCATION_ID = 'location:temple';
  const SET_ID = SET_BASE;
}

// class TowerLocation extends Location -- KS exclusive

// "Move another player's effort from here to any other location."
class TunnelsLocation extends Location
{
  const LOCATION_ID = 'location:tunnels';
  const SET_ID = SET_BASE;
}

// class TundraLocation extends Location -- KS exclusive

// class VoidLocation extends Location -- KS exclusive

// (No effect.)
class WastelandLocation extends Location
{
  const LOCATION_ID = 'location:wasteland';
  const SET_ID = SET_BASE;
}

// class UnderworldLocation extends Location -- KS exclusive
