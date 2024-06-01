<?php declare(strict_types=1);

// After the last effort is played and before scoring, all cards on
// locations are discarded.  The alchemist can then replace up to 3
// cards from their hand with 3 cards from the discard pile.
//
// - Notes: Pre-scoring choice.
const ALTERED_CLASS_ALCHEMIST = 'class:alchemist';

// Starts the game with 5 bot tokens, which cannot be moved and count
// as 5 Effort.  If the Artificer ends their turn with >= 4 Effort in
// any location, they can replace 4 Effort there with 1 bot token.
//
// - Notes: Turn-end choice.
const ALTERED_CLASS_ARTIFICER = 'class:artificer';

// XXX: Need to ask if the extra effort tokens themselves count as
// effort, or if the token allows the Barbarian to play another of
// their Effort cubes.
const ALTERED_CLASS_BARBARIAN = 'class:barbarian';

// Draws 3 cards at the start of the game.
const ALTERED_CLASS_BARD = 'class:bard';

// The Cleric's effort cannot be moved by other players; it can still
// be destroyed, and they can still move their own.
const ALTERED_CLASS_CLERIC = 'class:cleric';

// The Druid gains a +1 to all of their attributes during the end game
// scoring.
const ALTERED_CLASS_DRUID = 'class:druid';

// XXX: What is this?
const ALTERED_CLASS_DRAGON_SLAYER = 'class:dragonSlayer';

// Breaks all ties in their favor.  (If there are positive points on
// the line, only the Fighter gets them; if there is a tie for
// negative points, the Fighter doesn't get them.)
const ALTERED_CLASS_FIGHTER = 'class:figher';

// Can utilize all Item cards using one fewer Attribute card.  Stacks
// with Experience cards.
const ALTERED_CLASS_MERCHANT = 'class:merchant';

// Places their Mind Token at any location at the start of the game.
// On their turn, before placing Effort, they must move it to a
// different location.  The location with the Mind Token is treated as
// though it does not exist, including for the Monk.
//
// Note: Start-of-game choice.  Start-of-turn choice.
const ALTERED_CLASS_MONK = 'class:monk';

// May always choose to take one of the top 2 cards from the discard
// instead of taking a card from the location they visited each turn.
//
// Note: Card-draw choice.
const ALTERED_CLASS_NECROMANCER = 'class:necromancer';

// Starts the game with 2 "wild armor" tokens.  Each can be used as a
// wildcard piece of armor during end-game scoring.
const ALTERED_CLASS_PALADIN = 'class:paladin';

// +2 grit against all Threats.  (Not included if not playing with
// threats.)
const ALTERED_CLASS_RANGER = 'class:ranger';

// Ignores negative points from Settings and Threats.  They can also
// choose whether or not to move Effort.
const ALTERED_CLASS_ROGUE = 'class:rogue';

// Places 3 rift tokens at the beginning of the game.  If they place
// Effort on one location with a rift token, they may choose to take
// the action/card of another location with a rift token.
const ALTERED_CLASS_WIZARD = 'class:wizard';

// N.B.: SETTING_TRAVELING requires some pre-scoring user input from
//   all players.
