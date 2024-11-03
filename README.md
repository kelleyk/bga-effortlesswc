- Issues, blocking / high-sev

  - The value-selection bug still exists (but much more rarely?)!

  - UX/client issues

    - We need to fix the CSS issues that forced us to disable setloc tooltips.

    - We really need a "click the card in the play-area" selection mode for the situations where that's appropriate.

      - With the River, should we be able to see face-down cards?

    - Sequence animation a little bit.

    - Finish "show discard pile" feature.

      - Improve button.

  - Finish scoring table.

    - a subtotal line would be great

    - finish / fix-up armor and items sections

    - Add tooltips to icons?

    - "scoring seats" - need server to only send data for them

    - column widths are really wonky; we want equal widths for all of the columns other than the label column on the left

      - just give up and do this with javascript, or inline width styles?

    - text and icons are not vertically aligned; having an icon pushes the text down

  - Suggestions from Kat

    - Tooltips for icons (we need tooltips in general, really).

- Other

  - Clean up noisy client-side logging.

  - Go through all of the "tmp_*" CSS classes and see if we still need them.

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

  - Some of our client-side typing is a little iffy (use of "any", etc.).

  - The client-side input code does not support cancellation yet.  (Do we ever actually need this?)

- Future improvements

  - Server/client improvement: remember which seat(s) have seen which face-down card(s), and show those cards to the
    player in a "third state" ("face-down but you know what this is")?

- Things to mention in docs

  - Included content: at the designer/publisher's request, Kickstarter-exclusive content (Rings, Dragons) is not
    available.
