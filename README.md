- Remaining work/questions -- Highest priority

  - Need to improve `applyState()` to take care of TODOs (more types of updates) -- I think that flipping (e.g. when a
    face-down card is drawn to the hand) is probably the biggest gap.

  - We probably need to plumb the rest of our static data (in the assets repo in YAML files right now) into the game
    before we can really do scoring; for example, item details (utilization requirements, point value, and so on) are in
    there.

- UI concerns

  - With "River" (pick a card from any other location to discard) it can be hard to tell which card option is at which
    location.

  - Don't want slide-in animation when we refresh the page.

  - Cards sliding from a setloc up to the hand are invisible until they overlap the hand area.

  - For players other than the active player, cards on setlocs seem not to be positioned correctly after a card slides
    out.

- Remaining work/questions -- up next

  - Render reserve effort piles in the player boards.

  - Do we always take the single card on a location?

  - Need to improve static-data bundling so that we have names, tooltip text, etc. on client and server.

    - e.g. it'd be nice if `renderForNotif()` would include names rather than only IDs!

    - This blocks adding tooltips.

    - This blocks enriching log messages (e.g. with attr-stat and attr-card icons).

  - Need to add "general" icons (esp. "fame") to the spritesheet.

- Remaining work/questions -- backlog

  - What happens when we need to move cards between areas and they are scaled differently?
    - We're also using different jstpl templates for a card instantiated in each area.

  - When we move cards around, how will we generate notifs?
    - Does WcDeck need callback support?

  - Some of our client-side typing is a little iffy (use of "any", etc.).

  - The client-side input code does not support cancellation yet.  (Do we ever actually need this?)

  - No support for showing discard pile, other players' hands (in cooperative mode), etc.

  - The `renderForNotif()` implementations just show object IDs.

  - On the client-side, we need to recursively format templates until we reach a fixpoint.  Until we do, the data-driven
    messages in ST_INPUT will not appear correctly.

  - On the client-side, we need to draw the table-wide panel.

    - Including a "see discard pile" button.

  - In cooperative modes:

    - The table-wide panel should show score.

    - The player panels should have a "see this player's hand" button.

  - Server-side: there is no (BGA) scoring.

  - Server/client: there is no (in-game) scoring implemented yet.

  - Client: highlight last-drawn card(s).

- Rules questions

  - What happens when the deck is emptied?

  - When someone draws a face-down card, it stays secret from the other players, right?

- Future improvements

  - Server/client improvement: remember which seat(s) have seen which face-down card(s), and show those cards to the
    player in a "third state" ("face-down but you know what this is")?

- Things to mention in docs

  - Included content: at the designer/publisher's request, Kickstarter-exclusive content (Rings, Dragons) is not
    available.

- Things to test

  - When we visit Library (two face-down cards, pick one), we should be shown the cards face-up for selection
    purposes.

  - For locations such as Market, which require that you discard a card from your hand, you cannot visit if you do not
    have a card in your hand to discard.

    - We could add an "isVisitable()" function  to `Location` so that we don't let players select these locations in the first place.

  - Need to go through each call to the parameter-input system and check what happens when each exception is thrown (NoChoicesAvailable, InputRequired, InputCancelled, etc.).
