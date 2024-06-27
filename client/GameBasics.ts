// @ts-ignore
GameGui = /** @class */ (() => {
  function GameGui() {
    return;
  }
  return GameGui;
})();

/** Class that extends default bga core game class with more functionality
 */

// XXX: What's the purpose of `curstate` if we have `this.gamedatas.gamestate.name`?`

class GameBasics extends GameGui {
  protected curstate: string | null = null;
  protected pendingUpdate: boolean = false;
  protected currentPlayerWasActive: boolean = false;

  constructor() {
    super();
    console.log('game constructor');
  }

  // state hooks
  //
  // XXX: Improve typing
  public setup(gamedatas: any) {
    console.log('(BASICS) Starting game setup', gameui);
    this.gamedatas = gamedatas;
  }

  public onEnteringState(stateName: string, args: CurrentStateArgs) {
    // console.log("(BASICS) onEnteringState: " + stateName, args, this.debugStateInfo());

    this.curstate = stateName;
    // Call appropriate method
    args = args ? args.args : null; // this method has extra wrapper for args for some reason
    const methodName = 'onEnteringState_' + stateName;
    this.callfn(methodName, args);

    if (this.pendingUpdate) {
      this.onUpdateActionButtons(stateName, args);
      this.pendingUpdate = false;
    }
  }

  /* tslint:disable:no-unused-variable */
  public onLeavingState(stateName: string) {
    // console.log("(BASICS) onLeavingState: " + stateName, this.debugStateInfo());
    this.currentPlayerWasActive = false;
  }

  // XXX: from looking at
  // https://github.com/elaskavaia/bga-dojoless, it seems like this
  // function is meant to handle all dispatch of these events, not to be called via `super()`
  public onUpdateActionButtons(stateName: string, args: any) {
    console.log('(BASICS) onUpdateActionButtons()');
    if (this.curstate !== stateName) {
      // delay firing this until onEnteringState is called so they always called in same order
      this.pendingUpdate = true;
      // console.log('   DELAYED onUpdateActionButtons');
      return;
    }
    this.pendingUpdate = false;
    if (
      gameui.isCurrentPlayerActive() &&
      this.currentPlayerWasActive === false
    ) {
      console.log(
        'onUpdateActionButtons: ' + stateName,
        args,
        this.debugStateInfo(),
      );
      this.currentPlayerWasActive = true;
      // Call appropriate method
      this.callfn('onUpdateActionButtons_' + stateName, args);
    } else {
      this.currentPlayerWasActive = false;
    }
  }

  // utils
  protected debugStateInfo() {
    const iscurac = gameui.isCurrentPlayerActive();
    let replayMode = false;
    if (typeof g_replayFrom !== 'undefined') {
      replayMode = true;
    }
    const instantaneousMode = gameui.instantaneousMode ? true : false;
    const res = {
      instantaneousMode,
      isCurrentPlayerActive: iscurac,
      replayMode,
    };
    return res;
  }

  protected ajaxcallwrapper(action: string, args?: any, handler?: any) {
    if (!args) {
      args = {};
    }
    args.lock = true;

    if (gameui.checkAction(action)) {
      gameui.ajaxcall(
        '/' +
          gameui.game_name +
          '/' +
          gameui.game_name +
          '/' +
          action +
          '.html',
        args, //
        gameui,
        (result) => {
          return;
        },
        handler,
      );
    }
  }

  // protected createHtml(divstr: string, location?: string) {
  //   const tempHolder = document.createElement('div');
  //   tempHolder.innerHTML = divstr;
  //   const div = tempHolder.firstElementChild;
  //   const parentNode = document.getElementById(location);
  //   if (parentNode) {
  //     parentNode.appendChild(div);
  //   }
  //   return div;
  // }

  // protected createDiv(
  //   id?: string | undefined,
  //   classes?: string,
  //   location?: string,
  // ) {
  //   const div = document.createElement('div');
  //   if (id) {
  //     div.id = id;
  //   }
  //   if (classes) {
  //     div.classList.add(...classes.split(' '));
  //   }
  //   const parentNode = document.getElementById(location);
  //   if (parentNode) {
  //     parentNode.appendChild(div);
  //   }
  //   return div;
  // }

  // XXX: @kelleyk addition
  protected triggerUpdateActionButtons() {
    this.updatePageTitle();
  }

  /**
   *
   * @param {string} methodName
   * @param {object} args
   * @returns
   */
  protected callfn(methodName: string, args: any) {
    if (this[methodName] !== undefined) {
      console.log('Calling ' + methodName, args);
      return this[methodName](args);
    }
    return undefined;
  }

  /** @Override onScriptError from gameui */
  protected onScriptError(msg: string, url: string, linenumber: number) {
    if (gameui.page_is_unloading) {
      // Don't report errors during page unloading
      return;
    }

    // In any case, report these errors in the console
    console.error(msg);
    // cannot call super - dojo still have to used here
    //
    // super.onScriptError(msg, url, linenumber);
    return this.inherited(arguments);
  }
}
