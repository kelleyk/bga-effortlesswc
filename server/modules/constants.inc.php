<?php declare(strict_types=1);

// ------------------------
// Sets
// ------------------------

const SET_BASE = 'set:base';
const SET_ALTERED = 'set:altered';
const SET_HUNTED = 'set:hunted';

// ------------------------
// Global game-state types
// ------------------------

// The seat whose turn it currently is.  This is changed in ST_NEXT_TURN.
const GAMESTATE_INT_ACTIVE_SEAT = 'activeSeat';

// When a bot player would need to make a decision, the human players get to decide for them.  This is the ID of the
// player who will be asked to make the decision.  This rotates among the human players at the table in ST_NEXT_TURN.
const GAMESTATE_INT_DECIDING_PLAYER = 'decidingPlayer';

// XXX: For this game, which has a much simpler effect system, I think that we really only need to know which T_RET_*
// transition to take when we're done.
const GAMESTATE_JSON_RESOLVE_STACK = 'resolveStack';

const GAMESTATE_JSON_RESOLVE_VALUE_STACK = 'resolveValueStack';

const GAMESTATE_INT_ACTIVE_SEAT = 'activeSeat';

// ------------------------
// Transitions
// ------------------------

const T_DONE = 'tDone';
const T_END_GAME = 'tEndGame';
const T_BEGIN_HUMAN_TURN = 'tBeginHumanTurn';
const T_BEGIN_BOT_TURN = 'tBeginBotTurn';
const T_GET_INPUT = 'tGetInput';
const T_RESOLVE_LOCATION = 'tResolveLocation';
const T_ROUND_UPKEEP = 'tRoundUpkeep';
const T_TURN_UPKEEP = 'tEndUpkeep';

const T_RET_BOT_TURN = 'tRetBotTurn';
const T_RET_PLACE_EFFORT = 'tRetPlaceEffort';
const T_RET_PRE_SCORING = 'tRetPreScoring';
const T_RET_RESOLVE_LOCATION = 'tRetResolveLocation';

// ------------------------
// Game options
// ------------------------

const GAMEOPTION_DISABLED = 1;
const GAMEOPTION_ENABLED = 2;

const GAMEOPTION_RULESET = 100;
const GAMEOPTION_RULESET_COMPETITIVE = 1;
const GAMEOPTION_RULESET_COOPERATIVE = 2;

const GAMEOPTION_ALTERED_RACECLASS = 101;

const GAMEOPTION_HUNTED_THREATS = 102;

// ------------------------
// Game states
// ------------------------

const ST_BGA_GAME_SETUP = 1;

const ST_BGA_GAME_END = 99;

// Game state.  Setlocs are drawn, the decks are built and shuffled, etc.  Transitions to ST_ALTERED_INPUT.
const ST_INITIAL_SETUP = 2;

// Multi-active state.  (Or game state using ST_INPUT?)  Players choose races/classes.  Skipped if not playing with
// races/classes.  Transitions to ST_PREGAME.
//
// XXX: Or maybe we structure this like we structure ST_PRE_SCORING and merge it with ST_PREGAME?
const ST_ALTERED_INPUT = 15;

// Multi-active state.  (Or game state using ST_INPUT?)  If players have any pre-game decisions to make, they make them
// here.  Transitions to ST_NEXT_TURN.
const ST_PREGAME = 16;

// Game state.  The next seat in the turn order that has effort in its reserve is activated and a transition is taken to
// ST_PLACE_EFFORT (if the seat is occupied by a human player) or ST_BOT_TURN (if the seat is not).  If no seat has
// effort, transitions to ST_TRIGGER_END_GAME.
const ST_NEXT_TURN = 3;

// Single-active state.  The human player whose turn it is selects a setloc to visit; one effort from their seat's
// reserve is placed on that setloc.  Transitions to ST_RESOLVE_LOCATION.
//
// (XXX: Or is this a game state that uses ST_INPUT?)
const ST_PLACE_EFFORT = 4;

// Game state.  The effect of the visited location is resolved.  Transitions to ST_TURN_UPKEEP.
const ST_RESOLVE_LOCATION = 5;

// Game state.  Any end-of-turn effects are resolved here; then, transitions to ST_NEXT_TURN (or ST_ROUND_UPKEEP, if
// each seat has now taken a turn).
const ST_TURN_UPKEEP = 6;

// Game state.  Occurs after ST_TURN_UPKEEP every time each seat has taken a turn.
//
// Transitions to ST_DRAW_THREAT or to ST_NEXT_TURN.
//
// Game elements resolved here:
// - Threats
const ST_ROUND_UPKEEP = 7;

// XXX: Do we want these to all transition back to ST_ROUND_UPKEEP instead of directly to ST_NEXT_TURN?
//
// Game state.  Draws a threat and places it at a random location.  Transitions to ST_FIGHT_THREAT (if there is already
// a threat at that location), or to ST_NEXT_TURN (if there is not).
const ST_DRAW_THREAT = 8;

// XXX: Do we want these to all transition back to ST_ROUND_UPKEEP instead of directly to ST_NEXT_TURN?
//
// Game state.  Each player fights the threat, and is sent information about how that fight went.  Transitions to
// ST_NEXT_TURN.
//
// XXX: If we were going to have something like "sticky messages", this would be a great place to use that, so that each
// player sees the results of the fight and has to acknowledge it next time they see the table.
const ST_FIGHT_THREAT = 9;

// Game state.  Transitions to ST_PRE_SCORING.
const ST_TRIGGER_END_GAME = 10;

// Game state.  Acts for a seat that is not occupied by a human player.  Note that this state may still transition into
// ST_INPUT if human player(s) need to make decisions for the NPC seat.
const ST_BOT_TURN = 12;

// Multi-active state.  A player or group of players have been asked for input.
const ST_INPUT = 11;

// Game state.
//
// XXX: Should we use private states here so that players can make their own pre-scoring decisions?  Or do we need the
// players to move roughly in lockstep anyhow?
//
// XXX: Player(s) will be put in ST_INPUT here repeatedly until they have made all of their pre-scoring
// decisions.
//
// Once scoring can be completed, does so and transitions to ST_POST_SCORING.
//
// Game elements resolved here:
// - Alchemist (class)
// - Traveling (setting)
// - Secret (location) -- no input needed
const ST_PRE_SCORING = 13;

// Game state.  This state exists to serve information back to players about what happened during scoring (via state
// args).  Transitions to ST_BGA_GAME_END.
const ST_POST_SCORING = 14;

// ------------------------
// Classes from the "Altered" expansion
// ------------------------

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
