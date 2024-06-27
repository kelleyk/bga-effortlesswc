// // If you have any imports/exports in this file, 'declare global' is access/merge your game specific types with framework types. 'export {};' is used to avoid possible confusion with imports/exports.
// declare global {
/** @gameSpecific Add game specific notifications / arguments here. See {@link NotifTypes} for more information. */
export interface NotifTypes {
  // [name: string]: any; // Uncomment to remove type safety on notification names and arguments
}

export interface EffortPlacedNotif {}
export interface EffortMovedNotif {
  // XXX: Can this be the same type as `EffortPlacedNotif`, just with effort moving from a seat's reserve to a play
  // area?
}
export interface CardsMovedNotif {}
