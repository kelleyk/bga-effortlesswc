// import type { BgaGamedatas } from './bga-gamedatas';

/** @gameSpecific Add game specific gamedatas arguments here. See {@link Gamedatas} for more information. */
interface Gamedatas extends BgaGamedatas {
  // [key: string | number]: Record<keyof any, any>; // Uncomment to remove type safety on game state arguments

  mutableBoardState: MutableBoardState;

  immutableBoardState: ImmutableBoardState;
}

interface ImmutableBoardState {
  players: { [playerId: string]: PlayerPublic };
  // XXX: Do we need `playersPrivate`?
}

//
// When gamestates.jsonc is enabled in the config, the following types are automatically generated. And you should not add to anything to 'GameStates' or 'PlayerActions'. If gamestates.jsonc is enabled, 'GameStates' and 'PlayerActions' can be removed from this file.
//

interface GameStates {
  // [id: number]: string | { name: string, argsType: object} | any; // Uncomment to remove type safety with ids, names, and arguments for game states
}

interface MutableBoardState {
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

interface TableConfig {
  // XXX: cooperative/competitive?
  // XXX: which expansions are enabled?
}

// type CardState = 'FACEUP' | 'FACEDOWN';

// This is a `WcLib\CardBase`.
interface CardBase {
  id: number;

  location: string;
  sublocation: string;
  sublocationIndex: number;
  order: number;
  cardType: string | undefined;
  cardTypeGroup: string | undefined;
}

interface EffortlessLocation extends CardBase {
  effort: { [seatId: number]: number };
}

interface EffortlessSetting extends CardBase {}

// This is an Effortless "main-deck card".
interface Card extends CardBase {
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
interface PlayerPublic {
  id: string;
  no: number;
  name: string;
  color: string;
}

interface SeatBase {
  id: number;
  playerId: string | undefined; // Set iff there is a human player controlling this seat.
  seatColor: string;
  seatLabel: string;
}

// Sent to all players in all modes.
interface SeatPublic extends SeatBase {
  reserveEffort: number;
  colorName: string;
}

// Sent only to players about themselves in competitive mode; sent to all players in cooperative mode.
interface SeatPrivate {
  id: number;

  hand: Card[];
}

interface InputArgs {
  description: string;
  descriptionmyturn: string;
  cancellable: boolean;
  choices: any; // XXX: but always an array of int IDs so far?
  inputType: string;
}
