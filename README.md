- Blocking stuff

  - We really need a "click the card in the play-area" selection mode for the situations where that's appropriate.

    - With the River, should we be able to see face-down cards?

    - Can/should we also do a method for "cards in hand"?

  - Check out other competitive rulesets & player counts

- High-priority stuff that is not blocking

  - Sequence animation

  - Zombie turn (?) - randomly select from available choices?

  - Write docs

  - For unselectables, can we come up with a style where they appear greyed out but aren't transparent?  (As is, they
    look odd when overlapped.)

- Smaller stuff

  - The value-selection bug still exists (but much more rarely?)!

  - UX/client issues

    - Sequence animation a little bit.

  - Scoring table

    - add info icons with tooltips explaining scoring for each section

    - column widths are really wonky; we want equal widths for all of the columns other than the label column on the left

      - just give up and do this with javascript, or inline width styles?

    - text and icons are not vertically aligned; having an icon pushes the text down

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

  - Some of our client-side typing is a little iffy (use of "any", etc.).

  - The client-side input code does not support cancellation yet.  (Do we ever actually need this?)

- Future improvements

  - Server/client improvement: remember which seat(s) have seen which face-down card(s), and show those cards to the
    player in a "third state" ("face-down but you know what this is")?

- Things to mention in docs

  - Included content: at the designer/publisher's request, Kickstarter-exclusive content (Rings, Dragons) is not
    available.
