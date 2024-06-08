-- dbmodel.sql

-- This is the file where your are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- these export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here
--

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `gamestate` (
  `gamestate_key` VARCHAR(32) NOT NULL,

  -- Exactly one of these columns must be non-NULL.
  `gamestate_value_json` JSON DEFAULT NULL,
  `gamestate_value_int` INT(11) DEFAULT NULL,
PRIMARY KEY (`gamestate_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `seat` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
-- NOT NULL iff this is a player-controlled seat.
`player_id` int(10) UNSIGNED,
`seat_color` varchar(6) NOT NULL,
`seat_label` varchar(1) NOT NULL,

-- Game-specific values start here.
`reserve_effort` INT(10) UNSIGNED NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- XXX: As imported from Burgle Bros 2.
CREATE TABLE IF NOT EXISTS `card` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,

  -- These two strings uniquely identify the card-type.
  `card_type_group` VARCHAR(32) NOT NULL,
  `card_type` VARCHAR(32) NOT NULL,

  -- One of "CHARACTER", "GEAR", "PATROL", "DEADDROPS", "LOUNGE", "POOL".
  `card_location` VARCHAR(32) NOT NULL,

  -- One of "DECK", "DISCARD", "HAND", "PREPPED".
  --
  -- The "CHARACTER" location supports the "HAND", "PREPPED", and
  -- "DISCARD" sublocations.
  --
  -- The other locations support the "DECK" and "DISCARD"
  -- sublocations.
  `card_sublocation` VARCHAR(32) NOT NULL,

  -- When `card_location` is "CHARACTER", this is the `characterIndex`.
  -- When `card_location` is "PATROL", this is the Z coordinate (the
  -- zero-indexed floor number).  Otherwise, this must be NULL.
  `card_sublocation_index` INT(1),

  -- The order of the card within the (location, sublocation,
  -- location_index) area.  Lower numbers are "first", or closer to
  -- the top of a deck.
  --
  -- Values should be unique.  When they aren't, behavior is
  -- undefined, though we try to use `id` to break ties.
  `card_order` INT(10) NOT NULL,

  -- The number of times the card has been used.  When this number is
  -- >= the number of uses allowed by the card type, it "flips over":
  -- we show the corresponding gearBack image in the client and its
  -- ability changes to whatever the card's back specifies.  When it
  -- used again in that state, it is discarded.
  --
  -- Must be NULL except for prepped gear cards (cards in the
  -- "PREPPED" sublocation).
  `use_count` INT(1),
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
