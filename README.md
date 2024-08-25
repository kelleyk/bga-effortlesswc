- Issues

  - With the River, should we be able to see face-down cards?

  - Disable locations that we can't visit.  (Don't present them as valid choices.)

- Suggestions from Kat

  - Tooltips for icons (we need tooltips in general, really).

- Remaining work/questions -- Highest priority

  - Sequence animations a little bit.

  - Add attribute card & stat icons to log-entity replacement function.  (Do we have examples of places where we'd like
    to use this?)

  - Fix scoring for settings that were buggy in my game with Kat.

    - Also, "Hidden Market" is giving "seat 1" -15 points when all three seats have 0 effort on the location.

  - UI/UX

    - Make playable/unplayable effort piles more visible.

    - Reorder effort piles (and have them "slide out" a bit?) to show which seat(s) are currently affected by scoring.

    - Implement (client-side) a UI element to display scoring information.  Can be primitive; just need something
      functional.

    - We need a way to show Threat fights and end-game scoring.  Maybe we save a JSON object describing what happened
      and reference that from the log, notifs, etc.?

    - CSS layer/ordering thing.

  - Debt/cleanup

    - Go through all of the "tmp_*" CSS classes and see if we still need them.

    - Convert Gruntfile to TypeScript.

  - Give BGA points to players during end-game scoring.

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
