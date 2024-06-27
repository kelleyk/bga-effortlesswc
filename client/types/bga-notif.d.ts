// import type { PlayerIdString, Gamestate } from './bga-common';

/*
  The BGA framework sends events of these types for certain lifecycle
  events.  They can be subscribed to like any other notif.

  Here's a list of BGA framework event types (from the BGA Studio wiki):

    gameStateChange gameStateChangePrivateArg
    gameStateMultipleActiveUpdate newActivePlayer playerstatus
    yourturnack clockalert tableInfosChanged playerEliminated
    tableDecision archivewaitingdelay end_archivewaitingdelay
    replaywaitingdelay end_replaywaitingdelay
    replayinitialwaitingdelay end_replayinitialwaitingdelay
    aiPlayerWaitingDelay replay_has_ended updateSpectatorList
    wouldlikethink updateReflexionTime undoRestorePoint
    resetInterfaceWithAllDatas zombieModeFail zombieModeFailWarning
    aiError skipTurnOfPlayer zombieBack allPlayersAreZombie
    gameResultNeutralized playerConcedeGame showTutorial showCursor
    showCursorClick skipTurnOfPlayerWarning banFromTable
    resultsAvailable switchToTurnbased newPrivateState
    infomsg
  */

// "playerstatus"
type BgaPlayerStatusNotif = {
  player_id: PlayerIdString;
  player_name: string; // HTML around the actual player name.
  player_playing: boolean;
  player_status: string; // e.g. "online", "offline"
};

// "gameStateChange"
type BgaGameStateChangeNotif<T> = Gamestate<T>;

// "gameStateMultipleActiveUpdate"
//
// An array containing the IDs of the active players.
type BgaGameStateMultipleActiveUpdate = PlayerIdString[];

// "yourturnack"
type BgaYourTurnAckNotif = {
  player: PlayerIdString;
};
