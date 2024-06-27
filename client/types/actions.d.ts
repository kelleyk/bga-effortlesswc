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
