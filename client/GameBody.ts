class GameBody extends GameBasics {
  // playerHand: Stock;
  // playerCharacter: { [pcId: number]: PlayerCharacterView };
  // characterPanel: { [pcId: number]: CharacterPanelView };
  // panelManager: PanelManager;

  // cardUniqueIds: { [cardTypeGroup: string]: { [cardGroup: string]: number } };
  // cardTypesByUniqueId: { [cardTypeUid: number]: FQCardType };
  // nextCardUniqueId: number = 0;

  // // null if no tile is selected.
  // selectedTile: Position = null;

  // // XXX: Need a type for a BGA zone here.
  // zones: { [zoneType: string]: { [zoneId: string]: Zone } };

  constructor() {
    console.log('effortlesswc constructor');
    super();
  }

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

  // setupBoard(gamedatas: BurgleBrosTwoGamedatas) {
  //   // for (const floorMap of Object.values(gamedatas.gamemap.floors)) {
  //   //   console.log('*** setupBoard');
  //   //   console.log(z);
  //   //   console.log(floorMap);

  //   //   for (const tile of floorMap.tiles) {
  //   //     console.log(tile);
  //   //     // XXX: eliminate need for this
  //   //     var x = tile.pos[0];
  //   //     var y = tile.pos[1];
  //   //     var z = tile.pos[2];

  //   //     this.createTileContainer(tile);
  //   //     this.playTileOnTable(tile);
  //   //   }
  //   // }

  //   // for (const entity of gamedatas.entities) {
  //   //   this.createEntity(entity);
  //   // }

  //   // for (const [z, floorMap] of Object.entries(gamedatas.gamemap.floors)) {
  //   //   for (const wall of floorMap.walls) {
  //   //     this.createWall(wall);
  //   //   }
  //   // }
  // }

  // // XXX: When we ask players to choose characters, we
  // // need a player-scoped hand because we don't know how
  // // many characters they are playing yet.
  // setupPlayers(gamedatas) {
  //   /*
  //                  this.playerHands = {};
  //                  for (var playerId in gamedatas.players) {
  //                      var hand, handDivId, me = false;
  //                      if (playerId == this.playerId) {
  //                          this.myHand = new ebg.stock();
  //                          hand = this.myHand;
  //                          handDivId = 'myhand';
  //                          me = true;
  //                      } else {
  //                          this.playerHands[playerId] = new ebg.stock();
  //                          hand = this.playerHands[playerId];
  //                          handDivId = 'player_hand_' + playerId.toString();
  //                      }

  //                      hand.create( this, $(handDivId), this.cardwidth, this.cardheight);
  //                      hand.image_items_per_row = 2;
  //                      hand.onItemCreate = dojo.hitch(this, 'createCardZone', hand);
  //                      hand.centerItems = true;
  //                      if (me) {
  //                          hand.setSelectionMode(1);
  //                          hand.setSelectionAppearance('class');
  //                          dojo.connect( hand, 'onChangeSelection', this, 'handleCardSelected');
  //                      } else {
  //                          hand.setSelectionMode(0);
  //                      }

  //                      // this.addCardTypesToStock(hand, [0, 1, 2, 3]);
  //                      }
  //                  */

  //   console.log('cardsPerRow: ' + StaticData.cardsPerRow);

  //   this.playerHand = this.createHandStock($('myhand'), 'handleCardSelected');
  // }

  // createHandStock(divEl, onChangeSelection: string): Stock {
  //   // // XXX: Disabled because this causes an
  //   // // "undefined/no_stack_avail" error.
  //   // return;

  //   if (divEl === undefined || divEl == null) {
  //     throw 'Oops: cannot `createHandStock()` with null div.';
  //   }

  //   const hand = new ebg.stock();
  //   hand.create(this, divEl, /*cardWidth=*/ 125, /*cardHeight=*/ 125);
  //   hand.image_items_per_row = StaticData.cardsPerRow;
  //   // hand.onItemCreate = dojo.hitch(this, 'createCardZone', hand);
  //   hand.centerItems = true;

  //   hand.setSelectionMode(1);
  //   hand.setSelectionAppearance('class');
  //   dojo.connect(hand, 'onChangeSelection', this, onChangeSelection);

  //   /* this.addCardTypesToStock() */
  //   const cardTypeId = 42;
  //   const cardImageIndex = 3;
  //   hand.addItemType(cardTypeId, /*sortWeight=*/ cardTypeId, g_gamethemeurl + 'img/cards.jpg', cardImageIndex);

  //   this.loadCardTypes(hand);

  //   return hand;
  // }

  // setupStaticData() {
  //   console.log('setupStaticData()...');
  //   for (const [cardTypeGroup, cardTypes] of Object.entries(StaticData.cardImageIds)) {
  //     // console.log('loading static data for cardTypeGroup='+cardTypeGroup);
  //     for (const [cardType, cardTypeData] of Object.entries(cardTypes)) {
  //       // console.log('loading static data: cardTypeGroup='+cardTypeGroup+' cardType='+cardType);

  //       // N.B.: We do this to ensure that a unique
  //       // ID has been assigned to each card type
  //       ~~~~~(
  //         // so that we are ready to create stocks.
  //         this.getCardUniqueId(cardTypeGroup, cardType)
  //       );
  //     }
  //   }
  //   // console.log('setupStaticData(): done');
  // }

  // loadCardTypes(stock) {
  //   const this_ = this;
  //   // console.log('loadCardTypes(): entering...');
  //   for (const [cardTypeUid, cardGroupAndType] of Object.entries(this_.cardTypesByUniqueId)) {
  //     // console.log('uid = '+cardTypeUid+' cardGroupAndType=');
  //     // console.log(cardGroupAndType);
  //     const cardImageIndex = StaticData.cardImageIds[cardGroupAndType.cardTypeGroup][cardGroupAndType.cardType];
  //     // console.log('  imageIndex='+cardImageIndex);
  //     if (cardImageIndex === undefined) {
  //       throw 'Cannot load static data: card type has undefined image index.';
  //     }

  //     stock.addItemType(cardTypeUid, /*sortWeight=*/ cardTypeUid, g_gamethemeurl + 'img/cards.jpg', cardImageIndex);
  //   }
  //   // console.log('loadCardTypes(): done');
  // }

  // // XXX: these params should be renamed to `cardTypeGroup` and `cardType`.
  // getCardUniqueId(cardTypeGroup: string, cardType: string): number {
  //   // XXX: do we need this any longer?
  //   if (this.cardUniqueIds === undefined) {
  //     this.cardUniqueIds = {};
  //   }
  //   if (this.cardTypesByUniqueId === undefined) {
  //     this.cardTypesByUniqueId = {};
  //   }

  //   const cardGroupAndType: FQCardType = { cardTypeGroup, cardType };
  //   if (this.cardUniqueIds[cardTypeGroup] === undefined) {
  //     this.cardUniqueIds[cardTypeGroup] = {};
  //   }
  //   if (this.cardUniqueIds[cardTypeGroup][cardType] === undefined) {
  //     if (this.nextCardUniqueId === undefined) {
  //       this.nextCardUniqueId = 0;
  //     }
  //     const uniqueId = this.nextCardUniqueId++;

  //     this.cardUniqueIds[cardTypeGroup][cardType] = uniqueId;
  //     this.cardTypesByUniqueId[uniqueId] = cardGroupAndType;
  //   }
  //   return this.cardUniqueIds[cardTypeGroup][cardType];
  // }

  // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
  //                        action status bar (ie: the HTML links in the status bar).
  //
  public onUpdateActionButtons(stateName: string, args: any) {
    // // super.onUpdateActionButtons(stateName, args);
    // // console.log('onUpdateActionButtons(): '+stateName);
    // // console.log('onUpdateActionButtons(): curstate='+this.curstate);
    // // console.log(args);
    // if (this.isCurrentPlayerActive()) {
    //   switch (stateName) {
    //     case 'stCharacterSelection': {
    //       // Each player must select at least one character, and
    //       // there must be at least two characters selected overall.
    //       if (args.currentPlayerCharacterCount >= 1 && args.characterCount >= 2) {
    //         this.addActionButton('bPass', _('Pass'), () => this.ajaxcallwrapper('actPass'));
    //       }
    //       break;
    //     }
    //     case 'stPlayerTurn': {
    //       if (this.selectedTile !== null) {
    //         const selectedTile = this.encodePos(this.selectedTile);
    //         this.addActionButton('bMove', _('Move'), () => this.ajaxcallwrapper('actMove', { pos: selectedTile }));
    //         this.addActionButton('bPeek', _('Peek'), () => this.ajaxcallwrapper('actPeek', { pos: selectedTile }));
    //       }
    //       break;
    //     }
    //   }
    // }
  }

  // XXX: Make protected?
  public resetUi(): void {
    // // console.log('resetUI()...');
    // dojo.query('.tile').removeClass('tile-selectable');
  }

  // getTileEl(pos: Position): HTMLElement {
  //   return $('tile_' + pos[0] + '_' + pos[1] + '_' + pos[2]);
  // }
  // getTileContainerEl(pos: Position): HTMLElement {
  //   return $('tile_' + pos[0] + '_' + pos[1] + '_' + pos[2] + '_container');
  // }
  // getTileTokensEl(pos: Position): HTMLElement {
  //   return $('tile_' + pos[0] + '_' + pos[1] + '_' + pos[2] + '_tokens');
  // }
  // getEntityEl(entity: Entity): HTMLElement {
  //   return $(this.getEntityElId(entity));
  // }
  // getEntityElId(entity: Entity | number): string {
  //   if (isEntity(entity)) {
  //     return 'entity_' + entity.id;
  //   }
  //   return 'entity_' + entity;
  // }

  // setElVisibility(el: HTMLElement, visible: boolean): void {
  //   // XXX: Use `dojo.fx.Toggler` or similar to make this smooth
  //   // instead?
  //   if (visible) {
  //     el.classList.remove('hidden');
  //   } else {
  //     el.classList.add('hidden');
  //   }
  // }

  public rescaleSprite(el: HTMLElement, scale: number): void {
    el.style.height = 363.6 * scale + 'px';
    el.style.width = 233.4 * scale + 'px';

    const bgSize = 3334.2 * scale + 'px ' + 3328.2 * scale + 'px';
    console.log('*** bgSize = ', bgSize);
    el.style.backgroundSize = bgSize;

    el.style.backgroundPosition =
      -700.2 * scale + 'px ' + -1090.8 * scale + 'px';
  }

  public rescaleSpriteCube(el: HTMLElement, scale: number): void {
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

  public tintSprite(el: HTMLElement, color: string): void {
    el.style.backgroundBlendMode = 'multiply';
    el.style.backgroundColor = color;
  }

  // onEnteringState: this method is called each time we are entering into a new game state.
  //                  You can use this method to perform some user interface changes at this moment.
  //
  public onEnteringState(stateName: string, args: any) {
    super.onEnteringState(stateName, args);

    // const this_ = this;

    // console.log('onEnteringState(): ' + stateName);
    // console.log(args);

    // this.resetUi();

    // // XXX: move to some sort of event-hook system so that we don't
    // // have to manually plumb these around
    // this.panelManager.onEnteringState(stateName, args);

    // this.setElVisibility($('player_hand_wrap_1'), this.isPlayerHandVisibleInState(stateName));
    // this.setElVisibility($('character_hands_wrap'), this.areCharacterHandsVisibleInState(stateName));

    // switch (stateName) {
    //   case 'stCharacterSelection': {
    //     console.log('onEnteringState(): [stCharacterSelection]');
    //     // for (const [i, cardInst] of args.args.cards) {
    //     //     console.log('#'+i); console.log(cardInst);
    //     // }
    //     args.args.cards.forEach(function (cardInst, i) {
    //       // XXX: I'm not sure that these are the
    //       // right names for these things; compare to
    //       // DOCS.md.

    //       // console.log('#'+i); console.log(cardInst);
    //       const cardImage = cardInst['cardImage'];
    //       const cardTypeUid = this_.getCardUniqueId(cardImage[0], cardImage[1]);
    //       this_.playerHand.addToStockWithId(cardTypeUid, cardInst['id']);
    //       console.log('adding card to hand: cardImage=' + cardImage + ' cardTypeUid=' + cardTypeUid);
    //     });
    //     break;
    //   }

    //   // XXX: case 'stFinishSetup' -- clean up player hand

    //   case 'stPlaceEntranceTokens':
    //   case 'stPlayerTurnEnterMap': {
    //     // console.log('highlighting '+args.args.selectableTiles.length+' selectable tiles...');
    //     for (const pos of args.args.selectableTiles) {
    //       // console.log('  - selectable tile: ' + pos);
    //       this.getTileEl(pos).classList.add('tile-selectable');
    //     }
    //     break;
    //   }
    // }
  }

  // onLeavingState: this method is called each time we are leaving a game state.
  //                 You can use this method to perform some user interface changes at this moment.
  //
  public onLeavingState(stateName: string) {
    super.onLeavingState(stateName);
    // console.log('onLeavingState(): '+stateName);
  }

  // --- below this line, things are from the TS example ---

  // // on click hooks
  // onButtonClick(event) {
  //   console.log('onButtonClick', event);
  // }

  // onUpdateActionButtons_playerTurnA(args) {
  //     this.addActionButton("b1", _("Play Card"), () => this.ajaxcallwrapper("playCard"));
  //     this.addActionButton("b2", _("Vote"), () => this.ajaxcallwrapper("playVote"));
  //     this.addActionButton("b3", _("Pass"), () => this.ajaxcallwrapper("pass"));
  // }

  // onUpdateActionButtons_playerTurnB(args) {
  //     this.addActionButton("b1", _("Support"), () => this.ajaxcallwrapper("playSupport"));
  //     this.addActionButton("b2", _("Oppose"), () => this.ajaxcallwrapper("playOppose"));
  //     this.addActionButton("b3", _("Wait"), () => this.ajaxcallwrapper("playWait"));
  // }

  protected setupNotifications(): void {
    console.log('**setup notif**');
    // console.log(this);
    // console.log(this.notifqueue);

    for (const m in this) {
      if (typeof this[m] === 'function' && m.startsWith('notif_')) {
        dojo.subscribe(m.substring(6), this, m);
      }
    }

    // const bgaEvents = 'gameStateChange gameStateChangePrivateArg gameStateMultipleActiveUpdate newActivePlayer
    //   playerstatus yourturnack clockalert tableInfosChanged playerEliminated tableDecision archivewaitingdelay
    //   end_archivewaitingdelay replaywaitingdelay end_replaywaitingdelay replayinitialwaitingdelay
    //   end_replayinitialwaitingdelay aiPlayerWaitingDelay replay_has_ended updateSpectatorList wouldlikethink
    //   updateReflexionTime undoRestorePoint resetInterfaceWithAllDatas zombieModeFail zombieModeFailWarning aiError
    //   skipTurnOfPlayer zombieBack allPlayersAreZombie gameResultNeutralized playerConcedeGame showTutorial showCursor
    //   showCursorClick skipTurnOfPlayerWarning banFromTable resultsAvailable switchToTurnbased newPrivateState
    //   infomsg'.split(' ');

    //   // for (const i in bgaEvents) {
    //   //     let bgaEvent: string = bgaEvents[i];
    //   //     console.log(bgaEvent);
    //   //     dojo.subscribe(bgaEvent, this, (notif:any) => {
    //   //         this.onBgaEvent(bgaEvent, notif);
    //   //     });
    //   // }
  }

  // onBgaEvent(eventType: string, notif: Notif<any>) {
  //   console.log('*** BGA event: ' + eventType);
  //   console.log(notif);
  // }

  // // XXX: write an actual type for this notification
  // notif_characterSelected(notif: Notif<any>) {
  //   console.log('-*- notif: characterSelected');
  //   console.log(notif);
  //   // this.gamedatas.players[notif.args.player_id].character = notif.args.character;
  //
  //   this.playerHand.removeFromStockById(notif.args.cardId);
  //
  //   const pc: PlayerCharacter = notif.args.character;
  //   this.playerCharacter[pc.id] = new PlayerCharacterView(this, pc);
  //   this.characterPanel[pc.id] = new CharacterPanelView(this, pc);
  // }

  // notif_wallSpawns(notif: Notif<WallSpawnsNotif>) {
  //   // console.log("-*- notif: wallSpawns");
  //   // console.log(notif);
  //
  //   this.createWall(notif.args.wall);
  // }

  // // XXX: write an actual type for this notification
  // notif_entitySpawns(notif: Notif<EntitySpawnsNotif>) {
  //   // console.log("-*- notif: entitySpawns");
  //   // console.log(notif);
  //
  //   this.createEntity(notif.args.entity);
  // }

  // notif_entityUpdates(notif: Notif<EntityUpdatesNotif>) {
  //   // console.log("-*- notif: entityUpdates");
  //   // console.log(notif);
  //
  //   this.updateEntity(notif.args.entity);
  // }

  // notif_entityDespawns(notif: Notif<EntityDespawnsNotif>) {
  //   this.despawnEntity(notif.args.entityId);
  // }

  // notif_tileUpdates(notif: Notif<TileUpdatesNotif>) {
  //   // console.log("-*- notif: tileUpdates");
  //   // console.log(notif);
  //
  //   this.updateTile(notif.args.tile);
  // }

  // notifCheck_entitySpawns(notif: Notif<EntitySpawnsNotif>): boolean {
  //     console.log('-*- notifCheck: entitySpawns');
  //     console.log(notif);
  //
  //     if (notif.args.silent) {
  //         this.notif_entitySpawns(notif);
  //         return true;
  //     }
  //     return false;
  // }
}
