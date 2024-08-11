- Issues

  - With the River, should we be able to see face-down cards?

- Remaining work/questions -- Highest priority

  - We probably need to plumb the rest of our static data (in the assets repo in YAML files right now) into the game
    before we can really do scoring; for example, item details (utilization requirements, point value, and so on) are in
    there.

    - This is *mostly* done; but I should change the server-side

  - Need to add "general" icons (esp. "fame") to the spritesheet.

  - Add attribute card & stat icons to log-entity replacement function.

  - Render reserve effort piles in the player boards.

  - (Debt:) Reduce code duplication around sprite scaling.  Decide if we want to get rid of the ".tmp_card_scalable"
    class and related stuff.

  - (Debt:) Convert Gruntfile to TypeScript.

  - Take a pass at cleaning up log messages.

  - Implement (server-side) scoring for items.

  - Give BGA points to players during end-game scoring.

  - Implement (client-side) a UI element to display scoring information.  Can be primitive; just need something
    functional.

  - Take a peek at how the hand UI looks with ~20 (or a few more) cards in it.

  - Add card sorting within hands and setlocs.  (For setlocs, it's more about being able to always show threats on the
    right.)

  - From the BGA prerelease checklist:

    - When giving their turn to a player, you give them some extra time with the giveExtraTime() function.

    - Game progression is implemented (getGameProgression() in php)

    - Zombie turn is implemented (zombieTurn() in php). Note: it can only be tested if you explicitly click on the quit
      button to create a zombie. If you are expelled it does not generated a Zombie.

      - Zombie turn should just be randomly selecting a visitable location.  Before implementing Zombie behavior, we'll
        need to add an `isVisitable()` member to locations.  We should also use it to improve what we send the client.

      - We need to figure out what to do with decisions other than which location to visit.  Maybe also just a random
        choice?

    - You have defined and implemented some meaningful statistics for your game (i.e. total points, point from source A,
      B, C...)

    - You implemented tiebreaking (using aux score field) and updated tiebreaker description in meta-data

- UI concerns

  - Sometimes, after selecting a location to place Effort at, the location is not deselected when it becomes the next
    person's turn.

    - This is also now happening with e.g. effort-piles.

  - With "River" (pick a card from any other location to discard) it can be hard to tell which card option is at which
    location.

  - Don't want slide-in animation when we refresh the page.

    - Can we temporarily enable "instantaneous mode" (`this.page.instantaneousMode`) during page setup?

  - Cards sliding from a setloc up to the hand are invisible until they overlap the hand area.

- Remaining work/questions -- backlog

  - What happens when we need to move cards between areas and they are scaled differently?
    - We're also using different jstpl templates for a card instantiated in each area.

  - Some of our client-side typing is a little iffy (use of "any", etc.).

  - The client-side input code does not support cancellation yet.  (Do we ever actually need this?)

  - No support for showing discard pile, other players' hands (in cooperative mode), etc.

  - The `renderForNotif()` implementations just show object IDs.

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
