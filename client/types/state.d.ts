import type { BgaGamedatas } from './bga-gamedatas';

/** @gameSpecific Add game specific gamedatas arguments here. See {@link Gamedatas} for more information. */
export interface Gamedatas extends BgaGamedatas {
  // [key: string | number]: Record<keyof any, any>; // Uncomment to remove type safety on game state arguments

  mutableBoardState: MutableBoardState;

  immutableBoardState: ImmutableBoardState;
}

export interface ImmutableBoardState {
  players: { [playerId: string]: PlayerPublic };
  // XXX: Do we need `playersPrivate`?
}

//
// When gamestates.jsonc is enabled in the config, the following types are automatically generated. And you should not add to anything to 'GameStates' or 'PlayerActions'. If gamestates.jsonc is enabled, 'GameStates' and 'PlayerActions' can be removed from this file.
//

export interface GameStates {
  // [id: number]: string | { name: string, argsType: object} | any; // Uncomment to remove type safety with ids, names, and arguments for game states
}

export interface MutableBoardState {
  // players/seats (incl. which are human and which are bots)

  seats: { [seatId: number]: SeatPublic };
  cards: { [cardId: number]: Card };
  locations: { [locationId: number]: EffortlessLocation };
  settings: { [settingId: number]: EffortlessSetting };

  // cards
  // locations
  // settings

  // seatsPrivate: { [seatId: number]: PrivateSeatState };

  // tableConfig: TableConfig;

  // setlocs: { [setlocId: number]: SetlocState };
}

export interface TableConfig {
  // XXX: cooperative/competitive?
  // XXX: which expansions are enabled?
}

// type CardState = 'FACEUP' | 'FACEDOWN';

// This is a `WcLib\CardBase`.
export interface CardBase {
  id: number;

  location: string;
  sublocation: string;
  sublocationIndex: number;
  order: number;
  cardType: string | undefined;
  cardTypeGroup: string | undefined;
}

export interface EffortlessLocation extends CardBase {}

export interface EffortlessSetting extends CardBase {}

// This is an Effortless "main-deck card".
export interface Card extends CardBase {
  // state: CardState;

  // These properties are set iff `state == "FACEUP"`.
  cardTypeStem: string | undefined;

  faceDown: boolean;

  // attribute cards
  stat: string | undefined;
  points: number | undefined;

  // armor cards
  armorSet: string | undefined;
  armorPiece: string | undefined;

  // item cards
  itemNo: number | undefined;
}

// interface SetlocState {
//   setlocId: number; // 0-5
//
//   settingId: string;
//   locationId: string;
//   effort: { [seatId: number]: number }; // XXX: This won't support Laboratory, which has sub-locations.
//   cards: Card[];
// }

// XXX: Whenever possible, things should go in the `SeatPublic` type instead.
export interface PlayerPublic {
  id: string;
  no: number;
  name: string;
  color: string;
}

export interface SeatBase {
  id: number;
  playerId: string | undefined; // Set iff there is a human player controlling this seat.
  seatColor: string;
  seatLabel: string;
}

// Sent to all players in all modes.
export interface SeatPublic extends SeatBase {
  reserveEffort: number;
}

// Sent only to players about themselves in competitive mode; sent to all players in cooperative mode.
export interface SeatPrivate {
  id: number;

  hand: Card[];
}
