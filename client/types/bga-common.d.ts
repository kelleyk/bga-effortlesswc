// A string containing a decimal number.
type PlayerIdString = string;

interface Player {
  beginner: boolean;
  color: string;
  color_back: any | null;
  eliminated: number;
  id: PlayerIdString;
  is_ai: string;
  name: string;
  score: string;
  zombie: number;
}

interface Game {
  setup: (gamedatas: any) => void;
  onEnteringState: (stateName: string, args: any) => void;
  onLeavingState: (stateName: string) => void;
  onUpdateActionButtons: (stateName: string, args: any) => void;
  setupNotifications: () => void;
  //format_string_recursive: (log: string, args: any) => void;

  player_id: number; // XXX: PlayerIdString instead?
}

interface Notif<T> {
  args: T;
  log: string;
  move_id: number;
  table_id: string;
  time: number;
  type: string;
  uid: string;
}

interface Gamestate<T> {
  // The name of the state, as defined in your BGA state-machine.
  name: string;
  // Decimal number, but sent as a string.  (Edit: When sent as part
  // of the `gameStateChange` notif event this is an integer, not a
  // string.)
  id: string | number;
  // One of "game", "activeplayer", "multipleactiveplayer".
  type: string;

  // The name of the server-side "action" function invoked in this state.
  action: string;
  active_player: PlayerIdString;
  args: T;
  description: string;
  descriptionmyturn: string;
  possibleactions: Array<string>;
  updateGameProgression: number;
  transitions: { [transitionName: string]: number }; // Value is a state ID.

  // TODO: reflexion
  // TODO: multiactive (is an array containing... what? string player IDs?)
}
