/*
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * EffortlessWC implementation : © Kevin Kelley <kelleyk@kelleyk.net>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */
/// <amd-module name="bgagame/effortlesswc"/>

import Gamegui = require('ebg/core/gamegui');
import 'ebg/counter';

/** The root for all of your game code. */
class EffortlessWC extends Gamegui {
  // myGlobalValue: number = 0;
  // myGlobalArray: string[] = [];

  /** @gameSpecific See {@link Gamegui} for more information. */
  constructor() {
    super();
    console.log('effortlesswc constructor');
  }

  /** @gameSpecific See {@link Gamegui.setup} for more information. */
  public setup(gamedatas: Gamedatas): void {
    console.log('Starting game setup', gamedatas);

    // Setting up player boards
    for (const playerId in gamedatas.players) {
      if (gamedatas.players.hasOwnProperty(playerId)) {
        const player = gamedatas.players[playerId];
        // TODO: Setting up players boards if needed
        console.log(player);
      }
    }

    this.setupPlayArea(gamedatas.mutableBoardState);

    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();

    console.log('Ending game setup');
  }

  public setupPlayArea(mutableBoardState: MutableBoardState) {
    console.log('_', mutableBoardState);

    for (let i = 0; i < 6; ++i) {
      dojo.place(
        this.format_block('jstpl_setloc_panel', {
          classes: '',
          id: 'ewc_setloc_panel_' + i,
        }),
        $('ewc_setlocarea_column_' + (i % 2))!,
      );
    }

    document
      .querySelector('#ewc_setloc_panel_0 .ewc_setloc_location')!
      .classList.add('location_cabin');
    document
      .querySelector('#ewc_setloc_panel_1 .ewc_setloc_location')!
      .classList.add('location_forest');
    document
      .querySelector('#ewc_setloc_panel_2 .ewc_setloc_location')!
      .classList.add('location_garden');
    document
      .querySelector('#ewc_setloc_panel_3 .ewc_setloc_location')!
      .classList.add('location_river');
    document
      .querySelector('#ewc_setloc_panel_4 .ewc_setloc_location')!
      .classList.add('location_stables');
    document
      .querySelector('#ewc_setloc_panel_5 .ewc_setloc_location')!
      .classList.add('location_city');

    document
      .querySelector('#ewc_setloc_panel_0 .ewc_setloc_setting')!
      .classList.add('setting_battling');
    document
      .querySelector('#ewc_setloc_panel_1 .ewc_setloc_setting')!
      .classList.add('setting_secret');
    document
      .querySelector('#ewc_setloc_panel_2 .ewc_setloc_setting')!
      .classList.add('setting_traveling');
    document
      .querySelector('#ewc_setloc_panel_3 .ewc_setloc_setting')!
      .classList.add('setting_active');
    document
      .querySelector('#ewc_setloc_panel_4 .ewc_setloc_setting')!
      .classList.add('setting_eerie');
    document
      .querySelector('#ewc_setloc_panel_5 .ewc_setloc_setting')!
      .classList.add('setting_starved');

    // This function assumes that the matched element has a parent wrapper element.
    console.log('*** qsa ***');
    document.querySelectorAll('.tmp_scalable').forEach((rawEl: Element) => {
      const el = rawEl as HTMLElement;

      // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
      if (el.classList.contains('tmp_scaled')) {
        return;
      }
      el.classList.add('tmp_scaled');

      console.log('*** qsa foreach', rawEl);

      // const height = parseInt(el.style.height, 10);
      // const width = parseInt(el.style.width, 10);
      // console.log(el.style.height, el.style.width);
      // console.log(el.offsetHeight, el.offsetWidth);

      // const height = 123;
      // const width = 234;

      const scaleFactor = 0.5;

      this.rescaleSprite(el, scaleFactor);

      // // el.css(
      // //   '-webkit-transform',
      // //   'scale(' + scaleFactor + ', ' + scaleFactor + ')',
      // // );

      // // // In CSS, we'd call this e.g. "-webkit-transform".
      // // //
      // // // For details on these attributes, see https://stackoverflow.com/questions/708895/.
      // // //
      // // el.style.webkitTransform =
      // //   'scale(' + scaleFactor + ', ' + scaleFactor + ')';
      // // el.style.MozTransform = 'scale(' + scaleFactor + ', ' + scaleFactor + ')';

      // el.style.transform = 'scale(' + scaleFactor + ', ' + scaleFactor + ')';
      // el.style.transformOrigin = 'top left';

      // console.log(
      //   '  *** new dims ',
      //   el.offsetWidth * scaleFactor,
      //   el.offsetHeight * scaleFactor,
      // );

      // const parentEl = el.parentNode! as HTMLElement;
      // parentEl.style.width = el.offsetWidth * scaleFactor + 'px';
      // parentEl.style.height = el.offsetHeight * scaleFactor + 'px';

      // // XXX: is this also necessary, or...?
      // el.style.width = el.offsetWidth * scaleFactor + 'px';
      // el.style.height = el.offsetHeight * scaleFactor + 'px';
    });

    document
      .querySelectorAll('.tmp_scalable_cube')
      .forEach((rawEl: Element) => {
        const el = rawEl as HTMLElement;

        // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
        if (el.classList.contains('tmp_scaled_cube')) {
          return;
        }
        el.classList.add('tmp_scale_cube');

        this.rescaleSpriteCube(el, 0.6);
      });

    document.querySelectorAll('.tmp_tintable').forEach((rawEl: Element) => {
      const el = rawEl as HTMLElement;

      // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
      if (el.classList.contains('tmp_tinted')) {
        return;
      }
      el.classList.add('tmp_tinted');

      if (el.classList.contains('ewc_playercolor_teal')) {
        this.tintSprite(el, '#00b796');
      }
      if (el.classList.contains('ewc_playercolor_pink')) {
        this.tintSprite(el, '#ff5fa2');
      }
    });
  }

  public rescaleSprite(el: HTMLElement, scale: number) {
    el.style.height = 363.6 * scale + 'px';
    el.style.width = 233.4 * scale + 'px';

    const bgSize = 3334.2 * scale + 'px ' + 3328.2 * scale + 'px';
    console.log('*** bgSize = ', bgSize);
    el.style.backgroundSize = bgSize;

    el.style.backgroundPosition =
      -700.2 * scale + 'px ' + -1090.8 * scale + 'px';
  }

  public rescaleSpriteCube(el: HTMLElement, scale: number) {
    console.log('** rescaleSpriteCube()', el, scale);

    el.style.height = 30.0 * scale + 'px';
    el.style.width = 30.0 * scale + 'px';
    const spritesheetSize = 312.0 * scale + 'px ' + 302.4 * scale + 'px';
    console.log('*** bgSize = ', spritesheetSize);
    el.style.backgroundSize = spritesheetSize;
    el.style.maskSize = spritesheetSize;
    const spritesheetPos = -276.0 * scale + 'px ' + -121.2 * scale + 'px';
    el.style.backgroundPosition = spritesheetPos;
    el.style.maskPosition = spritesheetPos;
  }

  public tintSprite(el: HTMLElement, color: string) {
    el.style.backgroundBlendMode = 'multiply';
    el.style.backgroundColor = color;
  }

  ///////////////////////////////////////////////////
  //// Game & client states

  /** @gameSpecific See {@link Gamegui.onEnteringState} for more information. */
  public onEnteringState(
    stateName: GameStateName,
    args: CurrentStateArgs,
  ): void {
    console.log('Entering state', stateName, args);

    switch (stateName) {
      case 'dummmy':
        break;
    }
  }

  /** @gameSpecific See {@link Gamegui.onLeavingState} for more information. */
  public onLeavingState(stateName: GameStateName): void {
    console.log('Leaving state: ' + stateName);

    switch (stateName) {
      case 'dummmy':
        break;
    }
  }

  /** @gameSpecific See {@link Gamegui.onUpdateActionButtons} for more information. */
  public onUpdateActionButtons(
    stateName: GameStateName,
    args: AnyGameStateArgs | null,
  ): void {
    console.log('onUpdateActionButtons: ' + stateName, args);

    if (!this.isCurrentPlayerActive()) {
      return;
    }

    switch (stateName) {
      case 'dummmy':
        // Add buttons if needed
        break;
    }
  }

  ///////////////////////////////////////////////////
  //// Utility methods

  /*
		Here, you can defines some utility methods that you can use everywhere in your typescript
		script.
	*/

  ///////////////////////////////////////////////////
  //// Player's action

  /*
		Here, you are defining methods to handle player's action (ex: results of mouse click on game objects).

		Most of the time, these methods:
		- check the action is possible at this game state.
		- make a call to the game server
	*/

  /*
	Example:
	onMyMethodToCall1( evt: Event )
	{
		console.log( 'onMyMethodToCall1' );

		// Preventing default browser reaction
		evt.preventDefault();

		//	With base Gamegui class...

		// Check that this action is possible (see "possibleactions" in states.inc.php)
		if(!this.checkAction( 'myAction' ))
			return;

		this.ajaxcall( "/yourgamename/yourgamename/myAction.html", {
			lock: true,
			myArgument1: arg1,
			myArgument2: arg2,
		}, this, function( result ) {
			// What to do after the server call if it succeeded
			// (most of the time: nothing)
		}, function( is_error) {

			// What to do after the server call in anyway (success or failure)
			// (most of the time: nothing)
		} );


		//	With GameguiCookbook::Common...
		this.ajaxAction( 'myAction', { myArgument1: arg1, myArgument2: arg2 }, (is_error) => {} );
	}
	*/

  ///////////////////////////////////////////////////
  //// Reaction to cometD notifications

  /** @gameSpecific See {@link Gamegui.setupNotifications} for more information. */
  public setupNotifications() {
    console.log('notifications subscriptions setup');

    // TODO: here, associate your game notifications with local methods

    // With base Gamegui class...
    // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

    // With GameguiCookbook::Common class...
    // this.subscribeNotif( 'cardPlayed', this.notif_cardPlayed ); // Adds type safety to the subscription
  }

  /*
	Example:

	// The argument here should be one of there things:
	// - `Notif`: A notification with all possible arguments defined by the NotifTypes interface. See {@link Notif}.
	// - `NotifFrom<'cardPlayed'>`: A notification matching any other notification with the same arguments as 'cardPlayed'
	//   (A type can be used here instead). See {@link NotifFrom}.
	// - `NotifAs<'cardPlayed'>`: A notification that is explicitly a 'cardPlayed' Notif. See {@link NotifAs}.
	notif_cardPlayed( notif: NotifFrom<'cardPlayed'> )
	{
		console.log( 'notif_cardPlayed', notif );
		// Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
	}
	*/
}

// The global 'bgagame.effortlesswc' class is instantiated when the page is loaded. The following code sets this
// variable to your game class.
dojo.setObject('bgagame.effortlesswc', EffortlessWC);
// Same as: (window.bgagame ??= {}).effortlesswc = EffortlessWC;
