- Issues, blocking / high-sev

  - The value-selection bug still exists (but much more rarely?)!

  - UX/client issues

    - We need to fix the CSS issues that forced us to disable setloc tooltips.

    - We really need a "click the card in the play-area" selection mode for the situations where that's appropriate.

      - With the River, should we be able to see face-down cards?

    - The positioning of setlocs in the play-area is still a bit odd (need to check widths, maybe center stuff a bit, etc.).

    - Sequence animation a little bit.

    - Add "show discard pile" feature or remove the "(discard pile button)" thing.

  - Scoring:

    - Finish scoring table.

    - Add tooltips to icons?

    - "scoring seats" - need data from server

    - column widths are really wonky; we want equal widths for all of the columns other than the label column on the left

    - text and icons are not vertically aligned; having an icon pushes the text down

  - BGA integration

    - We probably need to give additional turn time.

    - Add metadata images.

  - Suggestions from Kat

    - Tooltips for icons (we need tooltips in general, really).

- Other

  - Debuggability

    - Add debug function that shows ID/name mappings for setlocs, seats, etc.?

  - Add attribute card & stat icons to log-entity replacement function.  (Do we have examples of places where we'd like
    to use this?)

  - Debt/cleanup

    - Go through all of the "tmp_*" CSS classes and see if we still need them.

    - Convert Gruntfile to TypeScript.

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

  - Don't want slide-in animation when we refresh the page.

    - Can we temporarily enable "instantaneous mode" (`this.page.instantaneousMode`) during page setup?

  - Cards sliding from a setloc up to the hand are invisible until they overlap the hand area.

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

- Future improvements

  - Server/client improvement: remember which seat(s) have seen which face-down card(s), and show those cards to the
    player in a "third state" ("face-down but you know what this is")?

- Things to mention in docs

  - Included content: at the designer/publisher's request, Kickstarter-exclusive content (Rings, Dragons) is not
    available.
