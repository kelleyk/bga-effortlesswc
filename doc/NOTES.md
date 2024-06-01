- Card library is going to need support for "state" (face-up or
  face-down) in some locations.  Alternatively, we could have two
  locations (loc-face-up, loc-face-down).

- Card locations: deck, discard, location[x], hand[x]

- We probably also need "card" decks for settings and locations.

- Colored effort cubes.
  (Do we want a pile of cubes, or will a count be fine?)

- For bots:

  "You take turns as normal, however: after each of your turns roll
  both of the dice and take turns for the other adventurers based on
  the numbers rolled. Place one Effort on the rolled Location and add
  a card to their hand. Anytime that the other adventurers would need
  to make a decision, whether it is moving Effort or choosing a card,
  you get to make that decision for them."

  - So maybe we assign a human to be the "decider" for each bot player?

- How do we want to show armor sets? (and items?)

- Game options:
  - "Fill with bots until player count..." (3+)
  - Cooperative / Competitive
  - Altered: Play with races and classes?
  - Hunted: Threats?

- Little detail rules:
  - Having 0 effort at a location counts as "the least".
  - If you can't discard a card, you can't visit a location that requires you to do so.
  - You may never move the effort you placed this turn.
  - Attribute cards *can* be reused to utilize multiple items.

- UI:
  - pick from cards on location(s) (Library, Coliseum, River)
  - pick from cards in hand (Market, Temple)
  - pick from cards in discard (Crypt)
  - pick a location and a player (Prison, Tunnels)
  - pick a location (Docks, City)

- UI widgets:

  - dice rolling (Mini Rogue?)

  - a nice "cards in hand" display

    https://stackoverflow.com/questions/71227308/css-how-to-make-an-effect-of-a-hand-holding-cards

    "Players will end the game with 20 cards and should keep their
    hands a secret from other players." -- So we're going to need to
    organize this a little bit!

    - Show number of attribute points of each type?
    - Show items and if they can be utilized.
    - Show armor grouped by set.

Components
  - "135 cards + 6 rings" (some of which are only used in 4+ player games)
  - "20 cubes and 1 disc in 5 colors"

- Card types
  - Armor sets: boots, gloves, helmet, chest piece.  Each piece of
    armor scores increasing points (1/4/8/13); each set is scored
    separately.
  - Items: score points if the player can "utilize" them, which
    requires the number of attribute cards shown.  (Double attribute
    cards do not count twice.)
  - Attributes (6 types): in single and double cards.  Strength,
    Dexterity, Constitution, Wisdom, Intelligence, and Charisma.

- Deck counts:
  - 3- player deck (109 cards):
    - 6x attr (8x single, 4x double)
    - 21 items
    - 4 armor sets: plate, obsidian, mage, leather
  - 4+ player deck (135 cards):
    - as above, but for each attr, 10x single and 5x double
    - two more armor sets: scale, assassin

- "Altered" expansion
  - Each player gets 1 of 10 races randomly.
  - Each player gets a choice between 2 of 12 classes randomly.
  - 6 experience cards
    - "Experience cards help players “utilize” Item Cards. When
      scoring Item Cards, players will assign each of their Experience
      Cards to one Item Card. Each will count as having one of the
      cards needed to score that Item.  Only one Experience Card can
      be used per Item Card.  Experience Cards increase the value of
      the Item Card they are used to complete by 2 Greatness.  These
      do NOT count as an attribute and will not effect Attribute
      scoring or fighting Threats."
  - 15 class tokens
    - 5 "bot" tokens for the Artificer
    - 4 "extra effort" tokens for the Barbarian
    - 2 "wild armor" tokens for the Paladin
    - 1 "mind"  token for the Monk
    - 3 "rift" tokens for the Wizard
  - Bard can have up to 23 cards in hand
  - 5 more settings and 5 more locations

  - Open issue: some classes not in rulebook
     - "Dragon Slayer" is secret

- "Hunted" expansion

  - 5 more settings and 5 more locations
  - 6 "grit" cards
  - 21 "threat" tiles
    - Have name, three weaknesses, a critical weakness.
    - The three weaknesses are always 2 card types and effort at the
      attached location.
    - The critical weakness is always a specific item.
  - 5 "vacant" tiles
  - 2 "threat scoring tiles"
  - 1 "class" tile

  - "Threats provide a new way to earn Greatness and introduce in-game
    scoring. They also allow for the game to be played cooperatively."

  - Should NOT be played with the regular "solo mode".

  - At the end of the round, after each player has taken a turn, roll
    a d6 to see where a Threat emerges.  If that location has been
    destroyed or removed, roll again.

  - Players will face all 21 threats during the game.

  - The threat discard pile is public and can be viewed by any player.

  - If there is a Vacant tile on the rolled location, no fight happens
    this round; discard the Vacant tile.

  - If there is already a threat at the rolled location, it attacks.
    Each player fights it independently.  If a player can deal at
    least enough damage to defeat the attacking Threat, they score
    greatness.

    - If only one player defeats it, they score 4 greatness;
      otherwise, each player who defeated it scores 2.

    - XXX: In competitive mode, do players have to reveal the cards
      that they use to defeat the Threat?

  - After the fight, the Threat is discarded, regardless of whether it
    was defeated or not.

  - Threats have 10 health (8 with 5 players).  Damage is
    - 1 per effort at the attached location
    - 1 per grit card in your hand
    - 1 per attribute card that matches the threat's weaknesses
    - 3 if you have the critical weakness item (which does not need to
      be utilized)

  - Cooperative:
     - Everyone is working together to score Fame, which comes from
       Greatness and defeating Threats.
     - Player hands are open information.
       - This has some UI implications!  We need to be able to show other hands to players!
     - Players win if they hit a fame threshold that varies by player
       count.
     - There are special solo rules.
