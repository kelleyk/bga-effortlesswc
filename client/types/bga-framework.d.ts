declare let gameui: GameGui;
declare let g_replayFrom: number | undefined;
declare let g_gamethemeurl: string;
declare let g_themeurl: string;
declare let g_archive_mode: boolean;
declare function _(str: string): string;
declare function __(site: string, str: string): string;
declare function $(text: string | Element): HTMLElement;

// declare const define;
// declare const ebg;
// declare const dojo;
// declare const dijit;
declare type eventhandler = (event?: any) => void;

type ElementOrId = Element | string;
type StringProperties = { [key: string]: string };

declare class GameNotifQueue {
  /**
   * Set the notification deinfed by notif_type as "synchronous"
   * @param notif_type - the type of notification
   * @param duration - the duration of notification wait in milliseconds
   * If "duration" is specified: set a simple timer for it (milliseconds)
   * If "duration" is not specified, the notification handler MUST call "setSynchronousDuration"
   */
  setSynchronous(notif_type: string, duration?: number): void;
  /**
   * Set dynamically the duration of a synchronous notification
   * MUST be called if your notification has not been associated with a duration in "setSynchronous"
   * @param duration - how long to hold off till next notficiation received (milliseconds)
   */
  setSynchronousDuration(duration: number): void;

  /**
   * Ignore notification if predicate is true
   * @param notif_type  - the type of notificatio
   * @param predicate - the function that if returned true will make framework not dispatch notification.
   * NOTE: this cannot be used for syncronious unbound notifications
   */
  setIgnoreNotificationCheck(
    notif_type: string,
    predicate: (notif: object) => boolean,
  ): void;
}

/*
declare interface Notif {
  type: string; // type of the notification (as passed by php function)
  log: string; // the log string passed from php notification
  args: any; // This is the arguments that you passed on your notification method on php
  bIsTableMsg: boolean; // is true when you use [[Main_game_logic:_yourgamename.game.php#NotifyAllPlayers|NotifyAllPlayers]] method (false otherwise)
  channelorig: string; // information about table ID (formatted as : "/table/t[TABLE_NUMBER]")
  gamenameorig: string; // name of the game
  move_id: number; // ID of the move associated with the notification
  table_id: number; // ID of the table (comes as string)
  time: number; // UNIX GMT timestamp
  uid: number; // unique identifier of the notification
  h: string; // unknown
  }
*/

declare class Counter {
  speed: number;

  create(target: string): void; //  associate counter with existing target DOM element
  getValue(): number; //  return current value
  incValue(by: number): number; //  increment value by "by" and animate from previous value
  setValue(value: number): void; //  set value, no animation
  toValue(value: number): void; // set value with animation
  disable(): void; // Sets value to "-"
}

declare class GameGui {
  page_is_unloading: any;
  game_name: string;
  instantaneousMode: boolean;
  player_id: number;
  interface_min_width: number;
  gamedatas: any;
  isSpectator: boolean;
  bRealtime: boolean;
  notifqueue: GameNotifQueue;
  last_server_state: any;
  scoreCtrl: { [player_id: number]: Counter };
  on_client_state: boolean;
  tooltips: string[];
  is_client_only: boolean;
  prefs: any[];
  table_id: number;
  metasiteurl: string;

  isCurrentPlayerActive(): boolean;
  getActivePlayerId(): number;
  addActionButton(
    id: string,
    label: string,
    method: string | eventhandler,
    destination?: string,
    blinking?: boolean,
    color?: string,
  ): void;
  checkAction(action: any): boolean;
  ajaxcall(
    url: string,
    args: object,
    bind: GameGui,
    resultHandler: (result: any) => void,
    allHandler: (err: any) => void,
  ): void;
  connect(node: ElementOrId, ontype: string, handler: any): void;
  disconnect(node: ElementOrId, ontype: string): void;
  connectClass(cls: string, ontype: string, handler: any): void;

  setup(gamedatas: object): void;
  onEnteringState(stateName: string, args: { args: any } | null): void;
  onLeavingState(stateName: string): void;
  onUpdateActionButtons(stateName: string, args: any): void;
  setupNotifications(): void;

  setClientState(newState: string, args: object): void;
  restoreServerGameState(): void;

  showMessage(msg: string, type: string): void;
  showMoveUnauthorized(): void;
  onScriptError(msg: string, url?: string, linenumber?: number): void;
  inherited(args: any): any;
  format_string_recursive(log: string, args: any[]): string;
  clienttranslate_string(text: string): string;

  onScreenWidthChange(): void;

  slideToObject(
    mobile_obj: string | Element,
    target_obj: string | Element,
    duration?: number,
    delay?: number,
  ): Animation;
  slideToObjectPos(
    mobile_obj: string | Element,
    target_obj: string | Element,
    target_x: number,
    target_y: number,
    duration?: number,
    delay?: number,
  ): Animation;
  slideTemporaryObject(
    mobile_obj_html: string,
    mobile_obj_parent: string | Element,
    from: string | Element,
    to: string | Element,
    duration?: number,
    delay?: number,
  ): Animation;

  displayScoring(
    anchor_id: string,
    color: string,
    score: number | string,
    duration?: number,
    offset_x?: number,
    offset_y?: number,
  ): void;
  showBubble(
    anchor_id: string,
    text: string,
    delay?: number,
    duration?: number,
    custom_class?: string,
  ): void;
  updateCounters(counters: any): void;

  addTooltip(
    nodeId: string,
    helpStringTranslated: string,
    actionStringTranslated: string,
    delay?: number,
  ): void;
  addTooltipHtml(nodeId: string, html: string, delay?: number): void;
  addTooltipHtmlToClass(cssClass: string, html: string, delay?: number): void;
  addTooltipToClass(
    cssClass: string,
    helpStringTranslated: string,
    actionStringTranslated: string,
    delay?: number,
  ): void;
  removeTooltip(nodeId: string): void;

  confirmationDialog(
    message: string,
    yesHandler: (param: any) => void,
    noHandler?: (param: any) => void,
    param?: any,
  ): void;
  multipleChoiceDialog(
    message: string,
    choices: any[],
    callback: (choice: number) => void,
  ): void;

  enablePlayerPanel(player_id: number): void;
  disablePlayerPanel(player_id: number): void;

  // XXX: Added by @kelleyk while porting JS to TS.
  format_block(msg: string, args: any): any;

  // XXX: Added by @kelleyk.
  //
  // Call after updating
  // e.g. `this.gamedatas.gamestate.descriptionmyturn`.  Triggers
  // another call to `onUpdateActionButtons()`.
  //
  // As a trick to update action buttons, you can call this function
  // without parameters.
  updatePageTitle(title?: string): void;

  fadeOutAndDestroy(node: any, duration?: number, delay?: number): void;

  /*
    __inherited: function l(e, t, n, r)​​​
activeShowOpponentCursor: function activeShowOpponentCursor()​​​
adaptPlayersPanels: function adaptPlayersPanels()​​​
adaptStatusBar: function adaptStatusBar()​​​
addActionButton: function addActionButton(t, i, n, o, a, s)​​​
addMoveToLog: function addMoveToLog(t, i)​​​
ajaxcall: function ajaxcall()​​​
applyArchiveCommentMarkup: function applyArchiveCommentMarkup(e)​​​
applyTranslationsOnLoad: function applyTranslationsOnLoad()​​​
archiveCommentAttachImageToElement: function archiveCommentAttachImageToElement(t, i, n)​​​
archiveGoToMove: function archiveGoToMove(e, t)​​​
buildScoreDlgHtmlContent: function buildScoreDlgHtmlContent(t)​​​
cancelPlannedWakeUp: function cancelPlannedWakeUp()​​​
cancelPlannedWakeUpCheck: function cancelPlannedWakeUpCheck()​​​
change3d: function change3d(t, i, n, o, a, s, r)​​​
checkAction: function checkAction(e, t)​​​
checkHotseatFocus: function checkHotseatFocus()​​​
checkIfArchiveCommentMustBeDisplayed: function checkIfArchiveCommentMustBeDisplayed()​​​
checkLock: function checkLock(e)​​​
checkPossibleActions: function checkPossibleActions(e)​​​
checkWakupUpInFourteenSeconds: function checkWakupUpInFourteenSeconds()​​​
checkWakups: function checkWakups()​​​
clearArchiveCommentTooltip: function clearArchiveCommentTooltip()​​​
closeTurnBasedNotes: function closeTurnBasedNotes()​​​
completesetup: function completesetup(t, i, n, o, a, s, r, l, d, c, h)​​​
constructor: function C()
​​​
declaredClass: "ebg.core.gamegui"
​​​
decodeHtmlEntities: function decodeHtmlEntities(e)​​​
destroyAllEbgControls: function destroyAllEbgControls()​​​
disableNextMoveSound: function disableNextMoveSound()​​​
disablePlayerPanel: function disablePlayerPanel(t)​​​
displayScores: function displayScores()​​​
displayTableWindow: function displayTableWindow(t, i, n, o, a, s)​​​
displayZombieBack: function displayZombieBack()​​​
doArchiveNextLog: function doArchiveNextLog()​​​
doNewArchiveCommentNext: function doNewArchiveCommentNext()​​​
doRedirectToMetasite: function doRedirectToMetasite()​​​
dontPreloadImage: function dontPreloadImage(e)​​​
eloEndOfGameAnimation: function eloEndOfGameAnimation()​​​
eloEndOfGameAnimationWorker: function eloEndOfGameAnimationWorker()​​​
enableAllPlayerPanels: function enableAllPlayerPanels()​​​
enablePlayerPanel: function enablePlayerPanel(t)​​​
ensureImageLoading: function ensureImageLoading()​​​
ensureSpecificGameImageLoading: function ensureSpecificGameImageLoading(t)​​​
ensureSpecificImageLoading: function ensureSpecificImageLoading(t)​​​
enter3dButton: function enter3dButton(e)​​​
getActivePlayerId: function getActivePlayerId()​​​
getActivePlayers: function getActivePlayers()​​​
getArchiveCommentsPointers: function getArchiveCommentsPointers()​​​
getCommentsViewedFromStart: function getCommentsViewedFromStart()​​​
getCursorInfos: function getCursorInfos(e)​​​
getGameStandardUrl: function getGameStandardUrl()​​​
getInherited: function f(e, t, n)​​​
getMediaRatingParams: function getMediaRatingParams(e)​​​
getPlayerTooltip: function getPlayerTooltip(e)​​​
getRanking: function getRanking()​​​
getReplayLogNode: function getReplayLogNode()​​​
getScriptErrorModuleInfos: function getScriptErrorModuleInfos()​​​
giveHotseatFocusTo: function giveHotseatFocusTo(t)​​​
hideIngameMenu: function hideIngameMenu()​​​
inherited: function d(e, t, n, r)​​​
init3d: function init3d()​​​
initArchiveIndex: function initArchiveIndex()​​​
initCommentsForMove: function initCommentsForMove(e)​​​
initHotseat: function initHotseat()​​​
insert_rankings: function insert_rankings(t)​​​
isCurrentPlayerActive: function isCurrentPlayerActive()​​​
isInstanceOf: function p(e)​​​
isInterfaceLocked: function isInterfaceLocked()​​​
isInterfaceUnlocked: function isInterfaceUnlocked()​​​
isPlayerActive: function isPlayerActive(e)​​​
leave3dButton: function leave3dButton(e)​​​
loadReplayLogs: function loadReplayLogs()​​​
loadTrophyToSplash: function loadTrophyToSplash(e)​​​
lockInterface: function lockInterface(t)​​​
lockScreenCounter: function lockScreenCounter()​​​
markTutorialAsSeen: function markTutorialAsSeen(t)​​​
newArchiveCommentSave: function newArchiveCommentSave()​​​
newArchiveCommentSaveModify: function newArchiveCommentSaveModify(t)​​​
ntf_aiError: function ntf_aiError(t)​​​
ntf_aiPlayerWaitingDelay: function ntf_aiPlayerWaitingDelay(e)​​​
ntf_allPlayersAreZombie: function ntf_allPlayersAreZombie(t)​​​
ntf_archivewaitingdelay: function ntf_archivewaitingdelay(e)​​​
ntf_banFromTable: function ntf_banFromTable(e)​​​
ntf_clockalert: function ntf_clockalert(e)​​​
ntf_end_archivewaitingdelay: function ntf_end_archivewaitingdelay(e)​​​
ntf_end_replayinitialwaitingdelay: function ntf_end_replayinitialwaitingdelay(e)​​​
ntf_end_replaywaitingdelay: function ntf_end_replaywaitingdelay(e)​​​
ntf_gameResultNeutralized: function ntf_gameResultNeutralized(e)​​​
ntf_gameStateChange: function ntf_gameStateChange(t)​​​
ntf_gameStateChangePrivateArgs: function ntf_gameStateChangePrivateArgs(e)​​​
ntf_gameStateMultipleActiveUpdate: function ntf_gameStateMultipleActiveUpdate(e)​​​
ntf_infomsg: function ntf_infomsg(t)​​​
ntf_newActivePlayer: function ntf_newActivePlayer(e)​​​
ntf_newPrivateState: function ntf_newPrivateState(t)​​​
ntf_playerConcedeGame: function ntf_playerConcedeGame(t)​​​
ntf_playerEliminated: function ntf_playerEliminated(e)​​​
ntf_playerStatusChanged: function ntf_playerStatusChanged(t)​​​
ntf_replay_has_ended: function ntf_replay_has_ended(e)​​​
ntf_replayinitialwaitingdelay: function ntf_replayinitialwaitingdelay(e)​​​
ntf_replaywaitingdelay: function ntf_replaywaitingdelay(e)​​​
ntf_resetInterfaceWithAllDatas: function ntf_resetInterfaceWithAllDatas(t)​​​
ntf_resultsAvailable: function ntf_resultsAvailable(e)​​​
ntf_showCursor: function ntf_showCursor(t)​​​
ntf_showCursorClick: function ntf_showCursorClick(e)​​​
ntf_showTutorial: function ntf_showTutorial(t)​​​
ntf_simplePause: function ntf_simplePause(e)​​​
ntf_skipTurnOfPlayer: function ntf_skipTurnOfPlayer(e)​​​
ntf_skipTurnOfPlayerWarning: function ntf_skipTurnOfPlayerWarning(t)​​​
ntf_switchToTurnbased: function ntf_switchToTurnbased(t)​​​
ntf_tableDecision: function ntf_tableDecision(e)​​​
ntf_tableInfosChanged: function ntf_tableInfosChanged(e)​​​
ntf_tableWindow: function ntf_tableWindow(e)​​​
ntf_undoRestorePoint: function ntf_undoRestorePoint(t)​​​
ntf_updateReflexionTime: function ntf_updateReflexionTime(e)​​​
ntf_updateSpectatorList: function ntf_updateSpectatorList(e)​​​
ntf_wouldlikethink: function ntf_wouldlikethink(e)​​​
ntf_yourTurnAck: function ntf_yourTurnAck(e)​​​
ntf_zombieBack: function ntf_zombieBack(t)​​​
ntf_zombieModeFail: function ntf_zombieModeFail(e)​​​
ntf_zombieModeFailWarning: function ntf_zombieModeFailWarning(e)​​​
onAiNotPlaying: function onAiNotPlaying(t)​​​
onArchiveAddComment: function onArchiveAddComment(e)​​​
onArchiveCommentContinueModeChange: function onArchiveCommentContinueModeChange(t)​​​
onArchiveCommentDisplayModeChange: function onArchiveCommentDisplayModeChange(e)​​​
onArchiveCommentMaximize: function onArchiveCommentMaximize(t)​​​
onArchiveCommentMinimize: function onArchiveCommentMinimize(t)​​​
onArchiveCommentPointElementClick: function onArchiveCommentPointElementClick(t)​​​
onArchiveCommentPointElementOnMouseEnter: function onArchiveCommentPointElementOnMouseEnter(t)​​​
onArchiveGoTo: function onArchiveGoTo(t)​​​
onArchiveGoToMoveDisplay: function onArchiveGoToMoveDisplay()​​​
onArchiveHistory: function onArchiveHistory(t)​​​
onArchiveNext: function onArchiveNext(e)​​​
onArchiveNextLog: function onArchiveNextLog(e)​​​
onArchiveNextTurn: function onArchiveNextTurn(e)​​​
onArchiveToEnd: function onArchiveToEnd(e)​​​
onArchiveToEndSlow: function onArchiveToEndSlow(e)​​​
onBackToMetasite: function onBackToMetasite()​​​
onBanSpectator: function onBanSpectator(t)​​​
onBeforeChatInput: function onBeforeChatInput(t)​​​
onBuyThisGame: function onBuyThisGame()​​​
onChangeContentHeight: function onChangeContentHeight()​​​
onChangePreference: function onChangePreference(e)​​​
onChangeRankMode: function onChangeRankMode(t)​​​
onChatInputBlur: function onChatInputBlur(e)​​​
onChatKeyDown: function onChatKeyDown(t)​​​
onClearNotes: function onClearNotes(t)​​​
onCloseTutorial: function onCloseTutorial(t)​​​
onCreateNewTable: function onCreateNewTable()​​​
onDecreaseExpelTime: function onDecreaseExpelTime(t)​​​
onEditReplayLogsComment: function onEditReplayLogsComment(t)​​​
onEditReplayLogsCommentSave: function onEditReplayLogsCommentSave(t)​​​
onEndDisplayLastArchive: function onEndDisplayLastArchive()​​​
onEndOfNotificationDispatch: function onEndOfNotificationDispatch()​​​
onEndOfReplay: function onEndOfReplay()​​​
onEnteringState: function onEnteringState(e, t)​​​
onFBReady: function onFBReady()​​​
onGameEnd: function onGameEnd()​​​
onGameUiWidthChange: function onGameUiWidthChange()​​​
onGlobalActionBack: function onGlobalActionBack(t)​​​
onGlobalActionFullscreen: function onGlobalActionFullscreen(t)​​​
onGlobalActionHelp: function onGlobalActionHelp()​​​
onGlobalActionPause: function onGlobalActionPause(e)​​​
onGlobalActionPreferences: function onGlobalActionPreferences()​​​
onGlobalActionQuit: function onGlobalActionQuit(t)​​​
onGsSocketIoConnectionStatusChanged: function onGsSocketIoConnectionStatusChanged(t, i)​​​
onHideCursor: function onHideCursor(t)​​​
onHotseatPlayButton: function onHotseatPlayButton(t)​​​
onHowToTutorial: function onHowToTutorial(t)​​​
onJudgeDecision: function onJudgeDecision(e)​​​
onKeyPressTutorial: function onKeyPressTutorial(t)​​​
onKeyUpTutorial: function onKeyUpTutorial(t)​​​
onLastArchivePlayed: function onLastArchivePlayed()​​​
onLeavingState: function onLeavingState(e)​​​
onLoadImageNok: function onLoadImageNok(e)​​​
onLoadImageOk: function onLoadImageOk(e)​​​
onLoadState: function onLoadState(e)​​​
onLockInterface: function onLockInterface(t)​​​
onMove: function onMove()​​​
onNewArchiveCommentCancel: function onNewArchiveCommentCancel(t)​​​
onNewArchiveCommentDelete: function onNewArchiveCommentDelete(t)​​​
onNewArchiveCommentDrag: function onNewArchiveCommentDrag(t)​​​
onNewArchiveCommentEndDrag: function onNewArchiveCommentEndDrag(t)​​​
onNewArchiveCommentModify: function onNewArchiveCommentModify(t)​​​
onNewArchiveCommentNext: function onNewArchiveCommentNext(t)​​​
onNewArchiveCommentSave: function onNewArchiveCommentSave(t)​​​
onNewArchiveCommentSaveModify: function onNewArchiveCommentSaveModify(t)​​​
onNewArchiveCommentStartDrag: function onNewArchiveCommentStartDrag(t)​​​
onNewLog: function onNewLog(e, t)​​​
onNextMove: function onNextMove(e)​​​
onNotPlayingHelp: function onNotPlayingHelp(e)​​​
onNotificationPacketDispatched: function onNotificationPacketDispatched()​​​
onPlayerDecide: function onPlayerDecide(e)​​​
onProposeRematch: function onProposeRematch()​​​
onPublishTutorial: function onPublishTutorial(t)​​​
onQuitTutorial: function onQuitTutorial(t)​​​
onReconnect: function onReconnect()​​​
onRemoveReplayLogsComment: function onRemoveReplayLogsComment(t)​​​
onReplayFromPoint: function onReplayFromPoint(t)​​​
onReplayLogClick: function onReplayLogClick(t)​​​
onRepositionPopop: function onRepositionPopop()​​​
onSaveNotes: function onSaveNotes(t)​​​
onSaveState: function onSaveState(e)​​​
onScreenWidthChange: function onScreenWidthChange()​​​
onSeeMoreLink: function onSeeMoreLink(t)​​​
onSeeMoreRanking: function onSeeMoreRanking(t)​​​
onShowGameHelp: function onShowGameHelp()​​​
onShowGameResults: function onShowGameResults()​​​
onShowMyCursor: function onShowMyCursor(t)​​​
onShowOpponentCursorMouseOver: function onShowOpponentCursorMouseOver(e)​​​
onShowStrategyHelp: function onShowStrategyHelp()​​​
onSkipPlayersOutOfTime: function onSkipPlayersOutOfTime(e)​​​
onStartGame: function onStartGame()​​​
onThumbUpLink: function onThumbUpLink(t)​​​
onTutoPointerClick: function onTutoPointerClick(t)​​​
onTutoRatingClick: function onTutoRatingClick(t)​​​
onTutoRatingEnter: function onTutoRatingEnter(t)​​​
onTutoRatingLeave: function onTutoRatingLeave(t)​​​
onTutorialClose: function onTutorialClose(e)​​​
onTutorialDlgClose: function onTutorialDlgClose(e)​​​
onUpdateActionButtons: function onUpdateActionButtons()​​​
onWouldFirePlayer: function onWouldFirePlayer(t)​​​
onWouldLikeToThink: function onWouldLikeToThink(e)​​​
onZombieBack: function onZombieBack(t)​​​
onZoomToggle: function onZoomToggle(t)​​​
openTurnBasedNotes: function openTurnBasedNotes(t)​​​
playMusic: function playMusic(e)​​​
prepareMediaRatingParams: function prepareMediaRatingParams()​​​
redirectToGamePage: function redirectToGamePage()​​​
redirectToLobby: function redirectToLobby()​​​
redirectToMainsite: function redirectToMainsite()​​​
redirectToTablePage: function redirectToTablePage()​​​
redirectToTournamentPage: function redirectToTournamentPage()​​​
registerEbgControl: function registerEbgControl(e)​​​
removeActionButtons: function removeActionButtons()​​​
removeArchiveCommentAssociatedElements: function removeArchiveCommentAssociatedElements()​​​
removeArchiveCommentPointElement: function removeArchiveCommentPointElement()​​​
replaceArchiveCursor: function replaceArchiveCursor()​​​
restoreClient: function restoreClient()​​​
restoreServerGameState: function restoreServerGameState()​​​
saveclient: function saveclient()​​​
sendNextArchive: function sendNextArchive()​​​
sendResizeEvent: function sendResizeEvent()​​​
sendWakeUpSignal: function sendWakeUpSignal()​​​
sendWakeupInTenSeconds: function sendWakeupInTenSeconds()​​​
setClientState: function setClientState(t, i)​​​
setLoader: function setLoader(t, i)​​​
setModeInstataneous: function setModeInstataneous()​​​
setup: function setup(e)​​​
setupCoreNotifications: function setupCoreNotifications()​​​
shouldDisplayClockAlert: function shouldDisplayClockAlert(e)​​​
showArchiveComment: function showArchiveComment(t, i)​​​
showEliminated: function showEliminated()​​​
showGameRatingDialog: function showGameRatingDialog()​​​
showIngameMenu: function showIngameMenu()​​​
showMoveUnauthorized: function showMoveUnauthorized()​​​
showNeutralizedGamePanel: function showNeutralizedGamePanel(t, i)​​​
showOpponentCursorClick: function showOpponentCursorClick(t)​​​
showOpponentCursorSendInfos: function showOpponentCursorSendInfos()​​​
showTutorial: function showTutorial()​​​
showTutorialActivationDlg: function showTutorialActivationDlg()​​​
showTutorialItem: function showTutorialItem(t)​​​
switchLogModeTo: function switchLogModeTo(t)​​​
switchToGameResults: function switchToGameResults()​​​
toggleIngameMenu: function toggleIngameMenu(t)​​​
toggleTurnBasedNotes: function toggleTurnBasedNotes()​​​
unactiveShowOpponentCursor: function unactiveShowOpponentCursor()​​​
unlockInterface: function unlockInterface(t)​​​
unsetModeInstantaneous: function unsetModeInstantaneous()​​​
updateActivePlayerAnimation: function updateActivePlayerAnimation()​​​
updateDecisionPanel: function updateDecisionPanel(t)​​​
updateFirePlayerLink: function updateFirePlayerLink()​​​
updateLoaderPercentage: function updateLoaderPercentage()​​​
updatePageTitle: function updatePageTitle(t)​​​
updatePlayerOrdering: function updatePlayerOrdering()​​​
updatePremiumEmblemLinks: function updatePremiumEmblemLinks()​​​
updatePubBanner: function updatePubBanner()​​​
updateReflexionTime: function updateReflexionTime(t)​​​
updateReflexionTimeDisplay: function updateReflexionTimeDisplay()​​​
updateResultPage: function updateResultPage()​​​
updateVisitors: function updateVisitors(t)


(... and there's more if you look at parent classes, but it starts to
look less relevant to a game)
        */
}
