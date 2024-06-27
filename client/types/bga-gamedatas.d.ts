import type { Player } from './bga-common';

export interface BgaGamedatas {
  // ------
  // These are standard members added by the BGA framework.
  // ------

  current_player_id: string; // XXX: Make PlayerIdString?
  decision: { decision_type: string };
  game_result_neutralized: string;
  neutralized_player_id: string; // XXX: Make PlayerIdString?
  notifications: { last_packet_id: string; move_nbr: string };
  playerorder: (string | number)[];
  players: { [playerId: number]: Player };
  tablespeed: string;

  // // XXX: The `any`s here are the state-specific args structs.
  // gamestate: Gamestate<any>;
  // gamestates: { [gamestateId: number]: Gamestate<any> };
}
