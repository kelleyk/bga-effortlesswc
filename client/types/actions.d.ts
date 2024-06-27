/** @gameSpecific Add game specific player actions / arguments here. See {@link PlayerActions} for more information. */
export interface PlayerActions {
  // [action: string]: Record<keyof any, any>; // Uncomment to remove type safety on player action names and arguments
}
// }

export interface PlaceEffortAction {}
// Used in any situation where the player is selecting a setloc, a card, effort, etc.
export interface SelectionAction {}
export interface CancelAction {
  // Deliberately empty.
}

// export {}; // Force this file to be a module.
