// // If you have any imports/exports in this file, 'declare global' is access/merge your game specific types with framework types. 'export {};' is used to avoid possible confusion with imports/exports.
// declare global {
/** @gameSpecific Add game specific notifications / arguments here. See {@link NotifTypes} for more information. */
interface NotifTypes {
  // [name: string]: any; // Uncomment to remove type safety on notification names and arguments
}

interface EffortPlacedNotif {}
interface EffortMovedNotif {
  // XXX: Can this be the same type as `EffortPlacedNotif`, just with effort moving from a seat's reserve to a play
  // area?
}
interface CardsMovedNotif {}

/** @gameSpecific Add game specific gamedatas arguments here. See {@link Gamedatas} for more information. */
interface Gamedatas {
  // [key: string | number]: Record<keyof any, any>; // Uncomment to remove type safety on game state arguments
}

//
// When gamestates.jsonc is enabled in the config, the following types are automatically generated. And you should not add to anything to 'GameStates' or 'PlayerActions'. If gamestates.jsonc is enabled, 'GameStates' and 'PlayerActions' can be removed from this file.
//

interface GameStates {
  // [id: number]: string | { name: string, argsType: object} | any; // Uncomment to remove type safety with ids, names, and arguments for game states
}

interface BoardState {
  // players/seats (incl. which are human and which are bots)

  players: { [playerId: string]: PublicPlayerState };
  // XXX: Do we need `playersPrivate`?

  seats: { [seatId: number]: PublicSeatState };
  seatsPrivate: { [seatId: number]: PrivateSeatState };

  tableConfig: TableConfig;

  setlocs: { [setlocId: number]: SetlocState };
}

interface TableConfig {
  // XXX: cooperative/competitive?
  // XXX: which expansions are enabled?
}

type CardState = 'FACEUP' | 'FACEDOWN';

interface Card {
  cardId: number;

  location: string;
  locationArg: number;

  state: CardState;

  // These properties are set iff `state == "FACEUP"`.
  cardType: string | undefined;
}

interface SetlocState {
  setlocId: number; // 0-5

  settingId: string;
  locationId: string;
  effort: { [seatId: number]: number }; // XXX: This won't support Laboratory, which has sub-locations.
  cards: Card[];
}

// XXX: Whenever possible, things should go in the `PublicSeatState` type instead.
interface PublicPlayerState {}

// Sent to all players in all modes.
interface PublicSeatState {
  seatId: number;

  name: string;
  playerId: string | undefined; // Set iff there is a human player controlling this seat.
  colorName: string;
  reserveEffort: number;
}

// Sent only to players about themselves in competitive mode; sent to all players in cooperative mode.
interface PrivateSeatState {
  seatId: number;

  hand: Card[];
}

/** @gameSpecific Add game specific player actions / arguments here. See {@link PlayerActions} for more information. */
interface PlayerActions {
  // [action: string]: Record<keyof any, any>; // Uncomment to remove type safety on player action names and arguments
}
// }

interface PlaceEffortAction {}
// Used in any situation where the player is selecting a setloc, a card, effort, etc.
interface SelectionAction {}
interface CancelAction {
  // Deliberately empty.
}

// export {}; // Force this file to be a module.
