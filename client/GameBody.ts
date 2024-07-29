/*
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Effortless implementation : © Kevin Kelley <kelleyk@kelleyk.net>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */
/// <amd-module name="bgagame/effortless"/>

// import Gamegui = require('ebg/core/gamegui');
// import 'ebg/counter';

/** The root for all of your game code. */
class GameBody extends GameBasics {
  // myGlobalValue: number = 0;
  // myGlobalArray: string[] = [];

  // These are cached copies of the last instance of each of these update messages the client has seen.
  protected mutableBoardState: MutableBoardState | null = null;
  protected privateState: PrivateState | null = null;

  protected tablewidePanelEl: HTMLElement | undefined = undefined;

  protected inputArgs: InputArgs | null = null;
  protected selectedLocation: number | null = null;
  protected selectedCard: number | null = null;
  protected selectedEffortPile: number | null = null;

  // XXX: Do we not have a good type for `ebg.zone`?
  protected handZone: any | null = null;
  protected locationZones: { [locationId: number]: any } = {};
  protected cardZoneObserver: MutationObserver;

  // Maps position (sublocationIndex) to location ID.
  protected locationByPos: { [pos: number]: EffortlessLocation } = {};

  /** @gameSpecific See {@link Gamegui} for more information. */
  constructor() {
    super();
    console.log('effortless constructor');

    this.cardZoneObserver = new MutationObserver(
      (records: MutationRecord[], _observer: MutationObserver) => {
        for (const record of records) {
          // console.log('mutation observed:', records, observer);
          for (const _el of Array.from(record.addedNodes)) {
            const el = _el as HTMLElement;
            this.onCardAddedToZone(
              el,
              this.getCardState(this.cardIdFromElId(el.id))!,
            );
          }
        }
      },
    );
  }

  /** @gameSpecific See {@link Gamegui.setup} for more information. */
  public override setup(gamedatas: Gamedatas): void {
    console.log('*** Entering `setup()`; gamedatas=', gamedatas);

    // XXX: Where should this go?
    //
    // XXX: Also, Zone is not responsive.
    const handZoneEl = document.querySelector(
      '#ewc_handarea #ewc_handarea_zone',
    )!;
    this.handZone = new ebg.zone();
    this.setUpCardZone(this.handZone, handZoneEl);

    // Setting up player boards
    for (const playerId in gamedatas.players) {
      if (gamedatas.players.hasOwnProperty(playerId)) {
        const player = gamedatas.players[playerId];
        // TODO: Setting up players boards if needed
        console.log(player);
      }
    }

    // TODO: Set up your game interface here, according to "gamedatas"

    this.setupSeatBoards(gamedatas.mutableBoardState);

    this.setupPlayArea(gamedatas.mutableBoardState);

    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();

    for (const z of this.allCardZones()) {
      this.cardZoneObserver.observe(z.container_div, { childList: true });
    }

    console.log('Ending game setup');
  }

  // XXX: Needs to be renamed.
  public onCardAddedToZone(el: HTMLElement, card: Card) {
    const cardMetadata = !card.visible
      ? null
      : StaticDataCards.cardMetadata[card.cardType!];
    if (cardMetadata !== null) {
      console.log('  metadata found; adding tooltip: ', el!.id, cardMetadata);
      this.addTooltipHtml(
        el!.id,
        this.format_block('jstpl_tooltip_card', cardMetadata),
      );
    } else {
      this.removeTooltip(el!.id);
    }
  }

  // This overrides a default implementation that BGA provides.
  public updatePlayerOrdering(): void {
    console.log('*** updatePlayerOrdering()');

    // N.B.: We start at 1 instead of at 0, like the default implementation does, so that our table-wide panel stays at
    // the top.  We should eventually improve this so that it handles seat boards as well.
    let place = 1;

    for (const i of Object.keys(this.gamedatas.playerorder)) {
      const playerId = this.gamedatas.playerorder[i];
      dojo.place('overall_player_board_' + playerId, 'player_boards', place);
      place++;
    }
  }

  public setupSeatBoards(mutableBoardState: MutableBoardState): void {
    console.log(
      'setupSeatBoards(): #player_boards =',
      $('player_boards').children.length,
    );

    this.tablewidePanelEl = dojo.place(
      this.format_block('jstpl_tablewide_panel', {}),
      $('player_boards'),
      'first',
    );

    // console.log(
    //   'setupSeatBoards(): after tablewide panel creation, player_boards =',
    //   $('player_boards').children,
    // );

    const reservePiles: { [seatId: number]: EffortPile } = {};
    for (const pile of Object.values(mutableBoardState.effortPiles)) {
      if (pile.locationId === null) {
        reservePiles[pile.seatId] = pile;
      }
    }

    for (const seat of Object.values(mutableBoardState.seats)) {
      if (seat.playerId === null) {
        dojo.place(
          this.format_block('jstpl_seat_board', {
            seatColor: seat.seatColor,
            seatId: seat.id,
            seatLabel: seat.seatLabel,
          }),
          $('player_boards'),
        );
      }

      const seatBoardEl = (
        seat.playerId === null
          ? document.querySelector(
              '#overall_seat_board_' + seat.id + ' .player_panel_content',
            )
          : document.querySelector(
              '#overall_player_board_' +
                seat.playerId +
                ' .player_panel_content',
            )
      ) as HTMLElement;

      // XXX: Need to find actual values for these params.
      dojo.place(
        this.format_block('jstpl_seat_board_contents', {
          colorName: seat.colorName,
          reservePileId: reservePiles[seat.id]!.id,
        }),
        seatBoardEl,
      );
    }

    // Hide scores, since we don't award points until the scoring phase at the end of the game.
    document.querySelectorAll('.player_score').forEach((el) => {
      (el as HTMLElement).style.visibility = 'hidden';
    });
  }

  public setupPlayArea(mutableBoardState: MutableBoardState) {
    for (const location of Object.values(mutableBoardState.locations)) {
      this.locationByPos[location.sublocationIndex] = location;
    }

    // Create the element that will display each setting-location pair and associated cards.
    for (let i = 0; i < 6; ++i) {
      const el = dojo.place(
        this.format_block('jstpl_setloc_panel', {
          classes: 'ewc_setloc_location_' + this.locationByPos[i]!.id,
          id: 'ewc_setloc_panel_' + i,
        }),
        $('ewc_setlocarea_column_' + (i % 2))!,
      );

      dojo.connect(
        el.querySelector('.ewc_setloc_setloc_wrap')!,
        'onclick',
        this,
        (evt: any) => {
          this.onClickLocation(evt, this.locationByPos[i]!.id);
        },
      );
    }

    for (const location of Object.values(mutableBoardState.locations)) {
      console.log('*** location', location);

      const parentEl = document.querySelector(
        '#ewc_setloc_panel_' + location.sublocationIndex + ' .ewc_setloc_cards',
      )!;

      const zone = new ebg.zone();
      this.setUpCardZone(zone, parentEl);

      this.locationZones[location.id] = zone;
    }

    // Create a counter for the amount of effort that each seat has on each location.
    for (const pile of Object.values(mutableBoardState.effortPiles)) {
      // Ignore reserve piles.
      if (pile.locationId !== null) {
        const seat = mutableBoardState.seats[pile.seatId]!;
        const location = mutableBoardState.locations[pile.locationId]!;

        const parentEl = document.querySelector(
          '#ewc_setloc_panel_' +
            location.sublocationIndex +
            ' .ewc_effort_counter_wrap',
        );

        const el = dojo.place(
          this.format_block('jstpl_effort_counter', {
            colorName: seat.colorName,
            id: pile.id,
          }),
          parentEl,
        );
        dojo.connect(el, 'onclick', this, (evt: any) => {
          this.onClickEffortPile(evt, pile.id);
        });
      }
    }

    this.applyState(mutableBoardState, /*privateState=*/ null);
  }

  public setUpCardZone(zone: any, el: any): void {
    // XXX: This is hardwired for now, but it should be the scaled size of these sprites.
    zone.create(this, el, 50, 185);
    zone.setPattern('custom');
    zone.itemIdToCoords = (i: number, controlWidth: number) => {
      // XXX: This spacing should probably be a percentage so that it's scaling-independent; what we care about is how
      // much of the card image we must show.
      const spacing = 35;

      const offset =
        (controlWidth - (zone.item_width + spacing * (zone.items.length - 1))) /
        2;

      return {
        h: zone.item_height,
        w: zone.item_width,
        x: offset + spacing * i,
        y: 0,
      };
    };
  }

  // This is the top-level state update function.  It's called each time we get an update from the server that includes
  // one or more of the state update messages (public and private) that the server generates.
  //
  // The parameters are optional because not every update includes all of these messages.  If a parameter isn't given,
  // we use the last copy of that message we've seen.
  //
  // Types of mutable state updates supported:
  // - cards entering and leaving the play area - TODO: needs to be animated as cards moving to/from discard & deck
  //
  // TODO: Types of state updates not yet supported, but that we'll need:
  // - update effort-pile counters
  // - cards moving from setloc to hand
  // - discarding and replacing a setloc
  // - flipping a card at a setloc in place
  // - a card flipping when a face-down card is drawn from a location
  // - cards moving from hand to setloc (?)
  // - cards moving from hand to discard
  //
  public applyState(
    mutableBoardState: MutableBoardState | null,
    privateState: PrivateState | null,
  ) {
    if (mutableBoardState === null || mutableBoardState === undefined) {
      mutableBoardState = this.mutableBoardState;
      console.log('applyState(): using cached mutableBoardState');
    } else {
      this.mutableBoardState = mutableBoardState;
      console.log('applyState(): using novel mutableBoardState');
    }
    if (privateState === null || privateState === undefined) {
      privateState = this.privateState;
      console.log('applyState(): using cached privateState');
    } else {
      this.privateState = privateState;
      console.log('applyState(): using novel privateState');
    }

    console.log(
      'using mutableBoardState & privateState:',
      mutableBoardState,
      privateState,
    );

    // XXX: We should finish eliminating this legacy update routine.
    if (mutableBoardState !== null) {
      console.log(
        'applyState(): updating mutableBoardState',
        mutableBoardState,
      );
      this.applyMutableBoardState(mutableBoardState);
    }

    // -----------
    // Cards
    // -----------

    const seenCardIds: { [cardId: number]: boolean } = {};

    for (const card of this.allCardState()) {
      switch (card.sublocation) {
        case 'SETLOC': {
          seenCardIds[card.id] = true;
          break;
        }
        case 'HAND': {
          seenCardIds[card.id] = true;
          break;
        }
      }
      this.placeCard(card);
    }

    console.log('*** seenCardIds:', seenCardIds);
    // XXX: We'll need to expand this to deal with cards leaving the hand, as well.
    document.querySelectorAll('.ewc_card_playarea').forEach((rawEl) => {
      const el = rawEl as HTMLElement;
      const cardId = this.cardIdFromElId(el.id);
      console.log('  - existent card ID:', el.id, cardId);

      if (!seenCardIds.hasOwnProperty(cardId)) {
        console.log('     not present in update; destroying el!');
        this.removeFromCardZones(el, /*destroy=*/ true);
      }
    });

    // -------
    // Rescaling (and tinting eventually?)
    // -------

    this.rescaleCardSprites();

    // -------
    // Update UI elements
    // -------
    this.refreshUiElements();
  }

  public allCardState(): Card[] {
    let cards: Card[] = [];
    // XXX: should we init these things with empty messages so that we don't need all of these null checks?
    if (this.privateState !== null) {
      cards = cards.concat(Object.values(this.privateState.cards));
    }
    if (this.mutableBoardState !== null) {
      cards = cards.concat(Object.values(this.mutableBoardState.cards));
    }
    return cards;
  }

  public getCardState(cardId: number): Card | null {
    for (const card of this.allCardState()) {
      if (card.id === cardId) {
        return card;
      }
    }
    return null;
  }

  // This is called to let UI elements react to changing data and contents.
  public refreshUiElements(): void {
    this.handZone.updateDisplay();
    for (const zone of Object.values(this.locationZones)) {
      zone.updateDisplay();
    }
  }

  // XXX: It'd be nice if we could eliminate the need for this.
  public cardIdFromElId(elId: string): number {
    const rxp = /^cardid_(\d+)$/;
    const m = rxp.exec(elId)!;
    return parseInt(m[1]!, 10);
  }

  // XXX: We should find a way to make this more generic.
  public rescaleCardSprites(): void {
    // This function assumes that the matched element has a parent wrapper element.
    console.log('*** qsa ***');
    document
      .querySelectorAll('.tmp_scalable_card')
      .forEach((rawEl: Element) => {
        const el = rawEl as HTMLElement;

        // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
        if (el.classList.contains('tmp_scaled_card')) {
          return;
        }
        el.classList.add('tmp_scaled_card');

        const scaleFactor = 0.5;
        this.rescaleSprite(el, scaleFactor);
      });
  }

  public applyMutableBoardState(mutableBoardState: MutableBoardState) {
    this.mutableBoardState = mutableBoardState;

    // XXX: This will probably need a little bit of work when we start supporting changes to and discarding of settings
    // and locations.

    for (const location of Object.values(mutableBoardState.locations)) {
      console.log('*** location', location);

      // XXX: We need the bang ("!") here because, if the card is not visible, we won't know its type.  These particular
      // cards are always visible, however.  Should we consider improving our types so that we have visible and
      // not-visible subtypes?
      document
        .querySelector(
          '#ewc_setloc_panel_' +
            location.sublocationIndex +
            ' .ewc_setloc_location',
        )!
        .classList.add(location.cardType!.replace(':', '_'));
    }

    for (const pile of Object.values(mutableBoardState.effortPiles)) {
      // // Ignore reserve piles.
      // if (pile.locationId !== null) {
      document.querySelector<HTMLElement>(
        '#ewc_effort_counter_' + pile.id + ' .ewc_effort_counter_value',
      )!.innerText = '' + pile.qty;
      // }
    }
    // XXX: We'll also need to update reserve piles once we draw them in player boards, of course.

    for (const setting of Object.values(mutableBoardState.settings)) {
      // console.log('*** setting', setting);

      // XXX: See above comment about "!".
      document
        .querySelector(
          '#ewc_setloc_panel_' +
            setting.sublocationIndex +
            ' .ewc_setloc_setting',
        )!
        .classList.add(setting.cardType!.replace(':', '_'));
    }

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

      // XXX: We have several versions of this color translation code between the client and server sides of the game.
      // We should find a better way to consolidate.
      if (el.classList.contains('ewc_playercolor_teal')) {
        this.tintSprite(el, '#00b796');
      }
      if (el.classList.contains('ewc_playercolor_pink')) {
        this.tintSprite(el, '#ff5fa2');
      }
      if (el.classList.contains('ewc_playercolor_blue')) {
        this.tintSprite(el, '#001489');
      }
      if (el.classList.contains('ewc_playercolor_yellow')) {
        this.tintSprite(el, '#ffe900');
      }
      if (el.classList.contains('ewc_playercolor_white')) {
        this.tintSprite(el, '#ffffff');
      }
    });
  }

  // Returns all zones that can contain play-area cards.
  public allCardZones() {
    let zones = [this.handZone];
    zones = zones.concat(Object.values(this.locationZones));
    return zones;
  }

  // Does not re-place `elId` if it is already in `zone`; handles removing it from another zone if it belongs to one.
  //
  // (If we place an element in a second zone without removing it from the first one, the two zones will "argue" over
  // the element.)
  public placeCardInZone(zone: any, el: HTMLElement) {
    if (!zone.isInZone(el.id)) {
      this.removeFromCardZones(el, /*destroy=*/ false);
      zone.placeInZone(el.id);
    }
  }

  // Removes the card `elId` from any zone(s) that it currently belongs to.
  //
  // If we destroy the corresponding element without removing it from a zone first, it will visually disappear, but it
  // will still be present in the zone's set of `items` and space will be left for it when the zone arranges its
  // contents.
  public removeFromCardZones(el: HTMLElement, destroy: boolean) {
    for (const z of this.allCardZones()) {
      z.removeFromZone(el.id, /*bDestroy=*/ false);
    }
    if (destroy) {
      this.fadeOutAndDestroy(el);
    }
  }

  public placeCard(card: Card): void {
    switch (card.sublocation) {
      case 'SETLOC':
      case 'HAND':
        break;
      default:
        // Card not in the play-area.
        return;
    }

    console.log('*** card', card);

    // XXX: We're going to need to deal with the fact that we have (unique) instances of cards in the play area, but
    // also (potentially not unique) copies of those cards in the prompt area and in tooltips.
    let el = document.getElementById('cardid_' + card.id);

    if (el === null) {
      // This is the table-wide panel in the top right.  If the card is not already in the play-area, we will spawn it
      // here and slide it into position; if it's leaving the play area, we'll slide it over here and then destroy it.
      const spawnEl = document.getElementById('tablewide_panel')!;

      const cardType = !card.visible ? 'back' : card.cardType;

      el = dojo.place(
        this.format_block('jstpl_playarea_card', {
          cardType,
          id: card.id,
        }),
        spawnEl,
      );
    }

    switch (card.sublocation) {
      case 'SETLOC': {
        console.log('  - in setloc');
        const location = this.locationByPos[card.sublocationIndex]!;
        this.placeCardInZone(this.locationZones[location.id], el!);
        break;
      }
      case 'HAND': {
        console.log('  - in hand');
        this.placeCardInZone(this.handZone, el!);
        break;
      }
      default: {
        console.log('  - other sublocation: ' + card.sublocation);
        break;
      }
    }

    // XXX: When a card is moved from a setloc zone to the hand zone, the tooltip stops working.
    {
      // Handle appearance changes (e.g. when the card flips over).
      const cardType = !card.visible ? 'back' : card.cardType;
      if (!el!.classList.contains('card_' + cardType)) {
        for (const className of Array.from(el!.classList).filter((x) => {
          return x.match(/^card_/);
        })) {
          el!.classList.remove(className);
        }
        el!.classList.add('card_' + cardType);

        // N.B.: This is necessary in order to recalculate all of the sprite-sheet offsets; without it, the appearance
        // of the card won't actually change, even though we've replaced the "card_*" CSS class.
        this.rescaleSprite(el!, 0.5);
      }

      this.onCardAddedToZone(el!, card);
    }
  }

  // XXX: The need for this is a bit unfortunate; we could eliminate it.
  public getSpriteName(el: HTMLElement): string {
    // console.log('*** getSpriteName()', el.classList);

    for (const className of Object.values(el.classList)) {
      console.log(className);
      if (className.match(/^card_/g)) {
        return className;
      }
    }
    throw new Error('XXX: Unable to find sprite name.');
  }

  public rescaleSprite(el: HTMLElement, scale: number) {
    const spriteName = this.getSpriteName(el);
    const spriteMetadata = StaticDataSprites.spriteMetadata[spriteName];

    // console.log('rescaleSprite for spriteName=', spriteName);

    // XXX: We should pull these numbers from static card data.

    el.style.height = spriteMetadata.height * scale + 'px';
    el.style.width = spriteMetadata.width * scale + 'px';

    const bgSize =
      StaticDataSprites.totalWidth * scale +
      'px ' +
      StaticDataSprites.totalHeight * scale +
      'px';
    // console.log('*** bgSize = ', bgSize);
    el.style.backgroundSize = bgSize;

    el.style.backgroundPosition =
      spriteMetadata.offsetX * scale +
      'px ' +
      spriteMetadata.offsetY * scale +
      'px';
  }

  public rescaleSpriteCube(el: HTMLElement, scale: number) {
    // console.log('** rescaleSpriteCube()', el, scale);

    el.style.height = 30.0 * scale + 'px';
    el.style.width = 30.0 * scale + 'px';
    const spritesheetSize = 312.0 * scale + 'px ' + 302.4 * scale + 'px';
    // console.log('*** bgSize = ', spritesheetSize);
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
  //// Log-message formatting

  /** @override */
  //
  // This override repeatedly substitutes arguments until the string does not change.  This is useful for situations
  // such as our ST_INPUT, where some of the values in `args` contain substitution patterns themselves.
  public override format_string_recursive(log: string, args: any) {
    console.log('XXX:', args);

    let lastLog: string;
    do {
      lastLog = log;

      log = this.inherited(this.format_string_recursive, arguments);
      // log = super.format_string_recursive(log, args);
    } while (log !== lastLog);

    return this.replaceLogEntities(log);
  }

  // XXX: This is a short-term stand-in.
  //
  // TODO: For entityTypes of location, setting we should create tooltips.
  public replaceLogEntities(log: string): string {
    return log.replace(
      /:([a-z0-9-_]+)=([a-z0-9-_]+?):/g,
      (_m, _entityType, entityName) => {
        console.log('match parts: ', _m, _entityType, entityName);
        return entityName.charAt(0).toUpperCase() + entityName.slice(1);
      },
    );
  }

  ///////////////////////////////////////////////////
  //// User input

  // XXX: Now that we have `this.inputArgs`, should we always use that?
  public updateSelectables(inputArgs: InputArgs) {
    console.log('*** updateSelectables()');
    document.querySelectorAll('.ewc_selectable').forEach((el) => {
      el.classList.remove('ewc_selectable');
    });
    document.querySelectorAll('.ewc_unselectable').forEach((el) => {
      el.classList.remove('ewc_unselectable');
    });
    document.querySelectorAll('.ewc_selected').forEach((el) => {
      el.classList.remove('ewc_selected');
    });

    switch (inputArgs.inputType) {
      case 'inputtype:location': {
        console.log('  *** inputtype:location');
        for (const id of inputArgs.choices) {
          document
            .querySelector(
              '.ewc_setloc_location_' + id + ' .ewc_setloc_setloc_wrap',
            )!
            .classList.add('ewc_selectable');
        }

        document
          .querySelectorAll('.ewc_setloc_setloc_wrap:not(.ewc_selectable)')
          .forEach((el) => {
            el.classList.add('ewc_unselectable');
          });

        break;
      }
      case 'inputtype:card': {
        console.log('  *** inputtype:card', inputArgs);

        this.placeAndWipeIn(
          this.format_block('jstpl_promptarea', {}),
          'ewc_promptarea_wrap',
        );

        for (const _card of Object.values(inputArgs.choices)) {
          // XXX: Hacky; we should instead fix our type definitions.
          const card = _card as Card;

          const cardType = !card.visible ? 'back' : card.cardType;

          const parentEl = document.querySelector(
            '.ewc_promptarea .ewc_promptarea_choices',
          )!;

          // XXX: We also need to make these .ewc_selectable; and we're going to wind up needing to do other stuff such
          // as attaching tooltips and on-click handlers.  We should move this into a reusable function.
          const el = dojo.place(
            this.format_block('jstpl_prompt_card', {
              cardType,
              id: card.id,
            }),
            parentEl,
          );
          this.rescaleSprite(el, 0.35);
          el.classList.add('ewc_selectable');
          dojo.connect(el, 'onclick', this, (evt: any) => {
            this.onClickCard(evt, card.id);
          });
        }

        // for (const id of inputArgs.choices) {
        //   document
        //     .querySelector(
        //       '.ewc_setloc_location_' + id + ' .ewc_setloc_setloc_wrap',
        //     )!
        //     .classList.add('ewc_selectable');
        // }

        break;
      }
      case 'inputtype:effort-pile': {
        // N.B.: The `EffortPile` type on the server can represent either a reserve effort pile or an effort pile on a
        // location.  When asked for input, though, we'll only be picking the latter type.

        console.log('  *** inputtype:effort-pile');
        for (const id of inputArgs.choices) {
          // XXX: Should we use classes rather than IDs here for consistency with other things?
          document
            .querySelector('#ewc_effort_counter_' + id)!
            .classList.add('ewc_selectable');
        }

        document
          .querySelectorAll('.ewc_effort_counter:not(.ewc_selectable)')
          .forEach((el) => {
            el.classList.add('ewc_unselectable');
          });

        break;
      }
      default: {
        throw new Error('Unexpected input type: ' + inputArgs.inputType);
      }
    }
  }

  // XXX: Pick better type than `any`
  //
  // XXX: Does this also need to check that the target is .ewc_selectable?
  public onClickLocation(evt: any, locationId: number): void {
    console.log('onClickLocation', evt);

    if (
      this.inputArgs === null ||
      this.inputArgs.inputType !== 'inputtype:location' ||
      !this.inputArgs.choices.includes(locationId)
    ) {
      return;
    }

    document.querySelectorAll('.ewc_selected').forEach((el) => {
      el.classList.remove('ewc_selected');
    });
    evt.currentTarget.classList.add('ewc_selected');
    this.selectedLocation = locationId;
    this.triggerUpdateActionButtons();
  }

  // XXX: Pick better type than `any`
  //
  // XXX: Does this also need to check that the target is .ewc_selectable?
  public onClickEffortPile(evt: any, pileId: number): void {
    console.log('onClickEffortPile', evt);

    console.log(
      '  clicked pile = ' + pileId + '; choices = ',
      this.inputArgs!.choices,
    );

    if (
      this.inputArgs === null ||
      this.inputArgs.inputType !== 'inputtype:effort-pile' ||
      !this.inputArgs.choices.includes(pileId)
    ) {
      return;
    }

    document.querySelectorAll('.ewc_selected').forEach((el) => {
      el.classList.remove('ewc_selected');
    });
    evt.currentTarget.classList.add('ewc_selected');
    this.selectedEffortPile = pileId;
    this.triggerUpdateActionButtons();
  }

  // XXX: Pick better type than `any`
  //
  // XXX: Does this also need to check that the target is .ewc_selectable?
  public onClickCard(evt: any, cardId: number): void {
    console.log('onClickCard', evt);

    // XXX: We could get rid of the weirdly-different-ness of the card input type if we made `choices` an array of ints
    // and then had a separate place to put the full metadata about the cards.

    if (
      this.inputArgs === null ||
      this.inputArgs.inputType !== 'inputtype:card'
    ) {
      return;
    }

    const choiceIds = this.inputArgs.choices.map((card: any) => {
      return card.id;
    });

    if (!choiceIds.includes(cardId)) {
      return;
    }

    document.querySelectorAll('.ewc_selected').forEach((el) => {
      el.classList.remove('ewc_selected');
    });
    evt.currentTarget.classList.add('ewc_selected');
    this.selectedCard = cardId;
    this.triggerUpdateActionButtons();
  }

  ///////////////////////////////////////////////////
  //// Utils

  // public locationIdFromElId(elId: string): number {
  //   const m = elId.match(/(\d+)$/)!;
  //   return parseInt(m[1], 10);
  // }

  ///////////////////////////////////////////////////
  //// Game & client states

  public override onEnteringState(stateName: string, args: any): void {
    console.log('Entering state', stateName, args);
    super.onEnteringState(stateName, args);

    if (args !== null && args.args !== null) {
      this.applyState(args.args.mutableBoardState, args.args._private);
    }

    switch (stateName) {
      case 'stInput': {
        if (this.isCurrentPlayerActive()) {
          console.log('*** stInput: ', args);
          this.inputArgs = args.args.input;
          this.updateSelectables(args.args.input);
        }
        break;
      }
      case 'stPostScoring': {
        console.log('*** stPostScoring: ', args);
        // // XXX: make this clear all selectablse
        // this.inputArgs = null;
        // this.updateSelectables(null);
        break;
      }
    }
  }

  /** @gameSpecific See {@link Gamegui.onLeavingState} for more information. */
  public override onLeavingState(stateName: string): void {
    console.log('Leaving state: ' + stateName);
    super.onLeavingState(stateName);

    this.inputArgs = null;

    document.querySelectorAll('.ewc_promptarea').forEach((el) => {
      this.wipeOutAndDestroy(el as HTMLElement);
    });

    switch (stateName) {
      case 'stInput': {
        break;
      }
    }
  }

  public override onUpdateActionButtons(
    stateName: string,
    args: any | null,
  ): void {
    console.log('onUpdateActionButtons()', stateName, args);

    if (!this.isCurrentPlayerActive()) {
      return;
    }

    switch (stateName) {
      case 'stInput': {
        this.addActionButton(
          'btn_input_confirm',
          _('Confirm'),
          () => {
            // XXX: This will only work for inputtype:location; we'll need to generalize it for other input types.
            let rpcParam = null;
            switch (this.inputArgs!.inputType) {
              case 'inputtype:location': {
                rpcParam = {
                  selection: JSON.stringify({
                    inputType: 'inputtype:location',
                    value: this.selectedLocation,
                  }),
                };
                break;
              }
              case 'inputtype:card': {
                rpcParam = {
                  selection: JSON.stringify({
                    inputType: 'inputtype:card',
                    value: this.selectedCard,
                  }),
                };
                break;
              }
              case 'inputtype:effort-pile': {
                rpcParam = {
                  selection: JSON.stringify({
                    inputType: 'inputtype:effort-pile',
                    value: this.selectedEffortPile,
                  }),
                };
                break;
              }
              default: {
                throw new Error('Unexpected input type.');
              }
            }
            console.log('confirmed!', rpcParam);
            this.ajaxCallWrapper('actSelectInput', rpcParam);
          },
          undefined,
          undefined,
          'blue',
        );

        let confirmReady = false;
        if (this.inputArgs !== null) {
          switch (this.inputArgs.inputType) {
            case 'inputtype:location': {
              confirmReady = this.selectedLocation !== null;
              break;
            }
            case 'inputtype:card': {
              confirmReady = this.selectedCard !== null;
              break;
            }
            case 'inputtype:effort-pile': {
              confirmReady = this.selectedEffortPile !== null;
              break;
            }
            default: {
              throw new Error('Unexpected input type.');
            }
          }
        }

        if (!confirmReady) {
          dojo.addClass('btn_input_confirm', 'disabled');
        }

        break;
      }
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
  public override setupNotifications() {
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

// // The global 'bgagame.effortless' class is instantiated when the page is loaded. The following code sets this
// // variable to your game class.
// dojo.setObject('bgagame.effortless', Effortless);
// Same as:
// (window.bgagame ??= {}).effortless = Effortless;
