"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
// This will be un-commented by the `copy_and_replace_ts_build` Grunt task.  See that task for details on why this is
// necessary.
/* @@WC_UNCOMMENT_BEGIN@@ */
 let GameGui = /** @class */ (() => {
   return () => {
     /* XXX: Deliberately empty. */
   };
 })();
/* @@WC_UNCOMMENT_END@@ */
/** Class that extends default bga core game class with more functionality
 */
// XXX: What's the purpose of `curstate` if we have `this.gamedatas.gamestate.name`?`
var GameBasics = /** @class */ (function (_super) {
    __extends(GameBasics, _super);
    function GameBasics() {
        var _this = _super.call(this) || this;
        _this.curstate = null;
        _this.pendingUpdate = false;
        _this.currentPlayerWasActive = false;
        console.log('(BASICS) game constructor');
        return _this;
    }
    // state hooks
    GameBasics.prototype.setup = function (gamedatas) {
        console.log('(BASICS) Starting game setup');
        this.gamedatas = gamedatas;
    };
    GameBasics.prototype.onEnteringState = function (stateName, args) {
        // console.log("(BASICS) onEnteringState: " + stateName, args, this.debugStateInfo());
        this.curstate = stateName;
        // Call appropriate method
        args = args ? args.args : null; // this method has extra wrapper for args for some reason
        var methodName = 'onEnteringState_' + stateName;
        this.callfn(methodName, args);
        if (this.pendingUpdate) {
            this.onUpdateActionButtons(stateName, args);
            this.pendingUpdate = false;
        }
    };
    GameBasics.prototype.onLeavingState = function (_stateName) {
        // console.log("(BASICS) onLeavingState: " + stateName, this.debugStateInfo());
        this.currentPlayerWasActive = false;
    };
    // XXX: from looking at
    // https://github.com/elaskavaia/bga-dojoless, it seems like this
    // function is meant to handle all dispatch of these events, not to be called via `super()`
    GameBasics.prototype.onUpdateActionButtons = function (stateName, args) {
        console.log('(BASICS) onUpdateActionButtons()');
        if (this.curstate !== stateName) {
            // delay firing this until onEnteringState is called so they always called in same order
            this.pendingUpdate = true;
            // console.log('   DELAYED onUpdateActionButtons');
            return;
        }
        this.pendingUpdate = false;
        if (gameui.isCurrentPlayerActive() &&
            this.currentPlayerWasActive === false) {
            console.log('onUpdateActionButtons: ' + stateName, args, this.debugStateInfo());
            this.currentPlayerWasActive = true;
            // Call appropriate method
            this.callfn('onUpdateActionButtons_' + stateName, args);
        }
        else {
            this.currentPlayerWasActive = false;
        }
    };
    // utils
    GameBasics.prototype.debugStateInfo = function () {
        var iscurac = gameui.isCurrentPlayerActive();
        var replayMode = false;
        if (typeof g_replayFrom !== 'undefined') {
            replayMode = true;
        }
        var instantaneousMode = gameui.instantaneousMode ? true : false;
        var res = {
            instantaneousMode: instantaneousMode,
            isCurrentPlayerActive: iscurac,
            replayMode: replayMode,
        };
        return res;
    };
    GameBasics.prototype.ajaxCallWrapper = function (action, args, skipCheckAction, handler) {
        if (skipCheckAction === void 0) { skipCheckAction = false; }
        if (!args) {
            args = {};
        }
        args.lock = true;
        if (skipCheckAction || gameui.checkAction(action)) {
            gameui.ajaxcall('/' +
                gameui.game_name +
                '/' +
                gameui.game_name +
                '/' +
                action +
                '.html', args, gameui, function (_result) {
                /*deliberately empty */
            }, handler);
        }
    };
    // createHtml(divstr: string, location?: string) {
    //   const tempHolder = document.createElement('div');
    //   tempHolder.innerHTML = divstr;
    //   const div = tempHolder.firstElementChild;
    //   const parentNode = document.getElementById(location);
    //   if (parentNode) parentNode.appendChild(div);
    //   return div;
    // }
    // createDiv(id?: string | undefined, classes?: string, location?: string) {
    //   const div = document.createElement('div');
    //   if (id) div.id = id;
    //   if (classes) div.classList.add(...classes.split(' '));
    //   const parentNode = document.getElementById(location);
    //   if (parentNode) parentNode.appendChild(div);
    //   return div;
    // }
    /** @Override onScriptError from gameui */
    GameBasics.prototype.onScriptError = function (msg, _url, _linenumber) {
        if (gameui.page_is_unloading) {
            // Don't report errors during page unloading
            return;
        }
        // In anycase, report these errors in the console
        console.error(msg);
        // // cannot call super - dojo still have to used here
        // super.onScriptError(msg, url, linenumber);
        return this.inherited(this.onScriptError, arguments);
    };
    // XXX: I've only been trying to get this building; fix the typing.
    // XXX: Support easing?
    GameBasics.prototype.wipeOutAndDestroy = function (node, args) {
        if (args === void 0) { args = {}; }
        if (typeof args.duration === 'undefined') {
            args.duration = 500;
        }
        if (this.instantaneousMode) {
            args.duration = Math.min(1, args.duration);
        }
        args.node = node;
        var anim = dojo.fx.wipeOut(args);
        dojo.connect(anim, 'onEnd', function (_node) {
            dojo.destroy(_node);
        });
        anim.play();
    };
    // XXX: I've only been trying to get this building; fix the typing.
    GameBasics.prototype.placeAndWipeIn = function (node, parentId, args) {
        if (args === void 0) { args = {}; }
        var el = dojo.place(node, parentId);
        dojo.setStyle(el, 'display', 'none');
        if (typeof args.duration === 'undefined') {
            args.duration = 500;
        }
        if (this.instantaneousMode) {
            args.duration = Math.min(1, args.duration);
        }
        args.node = el;
        var anim = dojo.fx.wipeIn(args);
        anim.play();
    };
    /**
     *
     * @param {string} methodName
     * @param {object} args
     * @returns
     */
    GameBasics.prototype.callfn = function (methodName, args) {
        if (this[methodName] !== undefined) {
            console.log('Calling ' + methodName, args);
            return this[methodName](args);
        }
        return undefined;
    };
    // XXX: @kelleyk addition
    GameBasics.prototype.triggerUpdateActionButtons = function () {
        this.updatePageTitle();
    };
    return GameBasics;
}(GameGui));
var StaticDataCards = /** @class */ (function () {
    function StaticDataCards() {
    }
    StaticDataCards.cardMetadata = {
        attr_str_1: { title: 'Strength +1', sortWeight: 1 },
        attr_dex_1: { title: 'Dexterity +1', sortWeight: 3 },
        attr_con_1: { title: 'Constitution +1', sortWeight: 5 },
        attr_wis_1: { title: 'Wisdom +1', sortWeight: 7 },
        attr_int_1: { title: 'Intelligence +1', sortWeight: 9 },
        attr_cha_1: { title: 'Charisma +1', sortWeight: 11 },
        attr_str_2: { title: 'Strength +2', sortWeight: 2 },
        attr_dex_2: { title: 'Dexterity +2', sortWeight: 4 },
        attr_con_2: { title: 'Constitution +2', sortWeight: 6 },
        attr_wis_2: { title: 'Wisdom +2', sortWeight: 8 },
        attr_int_2: { title: 'Intelligence +2', sortWeight: 10 },
        attr_cha_2: { title: 'Charisma +2', sortWeight: 12 },
        item_1: { title: 'Silver Sword', sortWeight: 200 },
        item_2: { title: 'Compact Crossbow', sortWeight: 201 },
        item_3: { title: 'Poison Antidote', sortWeight: 202 },
        item_4: { title: 'Binding Rope', sortWeight: 203 },
        item_5: { title: 'Phantom Lantern', sortWeight: 204 },
        item_6: { title: 'Loaded Dice', sortWeight: 205 },
        item_7: { title: 'Handy Cannon', sortWeight: 206 },
        item_8: { title: 'Reflective Shield', sortWeight: 207 },
        item_9: { title: 'Awakened Artifact', sortWeight: 208 },
        item_10: { title: 'Wooden Stake', sortWeight: 209 },
        item_11: { title: 'Glacial Spear', sortWeight: 210 },
        item_12: { title: 'Sea Trident', sortWeight: 211 },
        item_13: { title: 'Iron Horseshoes', sortWeight: 212 },
        item_14: { title: 'Holy Water', sortWeight: 213 },
        item_15: { title: 'Explosive Trap', sortWeight: 214 },
        item_16: { title: 'Woven Net', sortWeight: 215 },
        item_17: { title: 'Hypnotic Flute', sortWeight: 216 },
        item_18: { title: 'Crystal Goggles', sortWeight: 217 },
        item_19: { title: 'Burning Torch', sortWeight: 218 },
        item_20: { title: 'Music Box', sortWeight: 219 },
        item_21: { title: 'Cheese Wheel', sortWeight: 220 },
        armor_mage_head: { title: 'Armor: mage head', sortWeight: 100 },
        armor_mage_chest: { title: 'Armor: mage chest', sortWeight: 101 },
        armor_mage_hands: { title: 'Armor: mage hands', sortWeight: 102 },
        armor_mage_feet: { title: 'Armor: mage feet', sortWeight: 103 },
        armor_plate_head: { title: 'Armor: plate head', sortWeight: 110 },
        armor_plate_chest: { title: 'Armor: plate chest', sortWeight: 111 },
        armor_plate_hands: { title: 'Armor: plate hands', sortWeight: 112 },
        armor_plate_feet: { title: 'Armor: plate feet', sortWeight: 113 },
        armor_leather_head: { title: 'Armor: leather head', sortWeight: 120 },
        armor_leather_chest: { title: 'Armor: leather chest', sortWeight: 121 },
        armor_leather_hands: { title: 'Armor: leather hands', sortWeight: 122 },
        armor_leather_feet: { title: 'Armor: leather feet', sortWeight: 123 },
        armor_obsidian_head: { title: 'Armor: obsidian head', sortWeight: 130 },
        armor_obsidian_chest: { title: 'Armor: obsidian chest', sortWeight: 131 },
        armor_obsidian_hands: { title: 'Armor: obsidian hands', sortWeight: 132 },
        armor_obsidian_feet: { title: 'Armor: obsidian feet', sortWeight: 133 },
        armor_scale_head: { title: 'Armor: scale head', sortWeight: 140 },
        armor_scale_chest: { title: 'Armor: scale chest', sortWeight: 141 },
        armor_scale_hands: { title: 'Armor: scale hands', sortWeight: 142 },
        armor_scale_feet: { title: 'Armor: scale feet', sortWeight: 143 },
        armor_assassin_head: { title: 'Armor: assassin head', sortWeight: 150 },
        armor_assassin_chest: { title: 'Armor: assassin chest', sortWeight: 151 },
        armor_assassin_hands: { title: 'Armor: assassin hands', sortWeight: 152 },
        armor_assassin_feet: { title: 'Armor: assassin feet', sortWeight: 153 },
        threat_1: { title: 'Savage Werewolf', sortWeight: 500 },
        threat_2: { title: 'Soaring Griffin', sortWeight: 501 },
        threat_3: { title: 'Beastly Manticore', sortWeight: 502 },
        threat_4: { title: 'Towering Cyclops', sortWeight: 503 },
        threat_5: { title: 'Vengeful Wraith', sortWeight: 504 },
        threat_6: { title: 'Local Cheater', sortWeight: 505 },
        threat_7: { title: 'Weathered Gargoyle', sortWeight: 506 },
        threat_8: { title: 'Petrifying Gorgon', sortWeight: 507 },
        threat_9: { title: 'Eldritch Cthulhu', sortWeight: 508 },
        threat_10: { title: 'Bloodthirsty Vampire', sortWeight: 509 },
        threat_11: { title: 'Ooze Cube', sortWeight: 510 },
        threat_12: { title: 'Abyssal Kraken', sortWeight: 511 },
        threat_13: { title: 'Lurking Kelpie', sortWeight: 512 },
        threat_14: { title: 'Playful Imp', sortWeight: 513 },
        threat_15: { title: 'Stone Golem', sortWeight: 514 },
        threat_16: { title: 'Screeching Harpy', sortWeight: 515 },
        threat_17: { title: 'Guardian Cerberus', sortWeight: 516 },
        threat_18: { title: 'Scaly Basilisk', sortWeight: 517 },
        threat_19: { title: 'Giant Spider', sortWeight: 518 },
        threat_20: { title: 'Screaming Banshee', sortWeight: 519 },
        threat_21: { title: 'Rat Swarm', sortWeight: 520 },
        class_elf: { title: 'Elf', sortWeight: 1000 },
        class_dwarf: { title: 'Dwarf', sortWeight: 1001 },
        class_human: { title: 'Human', sortWeight: 1002 },
        class_gnome: { title: 'Gnome', sortWeight: 1003 },
        class_goblin: { title: 'Goblin', sortWeight: 1004 },
        class_orc: { title: 'Orc', sortWeight: 1005 },
        class_fairy: { title: 'Fairy', sortWeight: 1006 },
        class_alchemist: { title: 'Alchemist', sortWeight: 1100 },
        class_artificer: { title: 'Artificer', sortWeight: 1101 },
        class_barbarian: { title: 'Barbarian', sortWeight: 1102 },
        class_bard: { title: 'Bard', sortWeight: 1103 },
        class_cleric: { title: 'Cleric', sortWeight: 1104 },
        class_druid: { title: 'Druid', sortWeight: 1105 },
        class_fighter: { title: 'Fighter', sortWeight: 1106 },
        class_merchant: { title: 'Merchant', sortWeight: 1107 },
        class_monk: { title: 'Monk', sortWeight: 1108 },
        class_necromancer: { title: 'Necromancer', sortWeight: 1109 },
        class_paladin: { title: 'Paladin', sortWeight: 1110 },
        class_ranger: { title: 'Ranger', sortWeight: 1111 },
        class_rogue: { title: 'Rogue', sortWeight: 1112 },
        class_wizard: { title: 'Wizard', sortWeight: 1113 },
        xp: { title: 'Experience', sortWeight: 300 },
        grit: { title: 'Grit', sortWeight: 301 },
    };
    return StaticDataCards;
}());
var StaticDataSetlocs = /** @class */ (function () {
    function StaticDataSetlocs() {
    }
    StaticDataSetlocs.locationMetadata = {
        coliseum: {
            name: 'Coliseum',
            text: 'Take one card from here and discard the other.',
        },
        library: {
            name: 'Library',
            text: 'View both cards here and take 1.  Replace the missing card face-down.',
        },
        market: {
            name: 'Market',
            text: 'Discard a card from your hand and take both cards here.',
        },
        cave: { name: 'Cave', text: '' },
        river: { name: 'River', text: 'Discard a card at another location.' },
        prison: {
            name: 'Prison',
            text: 'Move another player’s effort from any other location to here.',
        },
        tunnels: {
            name: 'Tunnels',
            text: 'Move another player’s effort from here to any other location.',
        },
        city: {
            name: 'City',
            text: 'Move one of your effort from any other location to here.',
        },
        wasteland: { name: 'Wasteland', text: '' },
        docks: {
            name: 'Docks',
            text: 'Move one of your effort from here to any other location.',
        },
        temple: {
            name: 'Temple',
            text: 'Discard a card from your hand to take the top 2 cards from the deck.',
        },
        crypt: {
            name: 'Crypt',
            text: 'Take 1 of the top 2 cards from the discard.',
        },
        tundra: {
            name: 'Tundra',
            text: 'Once all players have placed half of their effort, replace this location at random.',
        },
    };
    StaticDataSetlocs.settingMetadata = {
        active: { name: 'Active', text: 'Most here gains 4 points.' },
        crowded: {
            name: 'Crowded',
            text: 'Gain 10 points if you have at least 5 effort here.',
        },
        lively: { name: 'Lively', text: 'Gain 1 point for each effort here.' },
        peaceful: {
            name: 'Peaceful',
            text: 'Gain 3 points for every 2 effort here.',
        },
        battling: { name: 'Battling', text: 'Most here gains 8 points.' },
        barren: { name: 'Barren', text: '' },
        hidden: { name: 'Hidden', text: 'Least here loses 5 points.' },
        treacherous: {
            name: 'Treacherous',
            text: 'Lose 1 point for each effort here.',
        },
        quiet: { name: 'Quiet', text: 'Most here loses 5 points.' },
        eerie: { name: 'Eerie', text: 'Least here gains 5 points.' },
        holy: { name: 'Holy', text: 'Most here gains 2 points for each effort.' },
        ghostly: {
            name: 'Ghostly',
            text: 'Lose 2 points for every 2 effort here.',
        },
        frozen: {
            name: 'Frozen',
            text: 'Once all players have placed half of their effort, replace this setting at random.',
        },
    };
    return StaticDataSetlocs;
}());
var StaticDataSprites = /** @class */ (function () {
    function StaticDataSprites() {
    }
    StaticDataSprites.totalWidth = 2700;
    StaticDataSprites.totalHeight = 2613.6;
    StaticDataSprites.spriteMetadata = {
        card_armor_assassin_chest: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: 0,
        },
        card_armor_assassin_feet: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -233.4,
        },
        card_armor_assassin_hands: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -466.8,
        },
        card_armor_assassin_head: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -700.2,
        },
        card_armor_leather_chest: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -933.6,
        },
        card_armor_leather_feet: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -1167,
        },
        card_armor_leather_hands: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -1400.4,
        },
        card_armor_leather_head: {
            width: 150,
            height: 233.4,
            offsetX: 0,
            offsetY: -1680,
        },
        card_armor_mage_chest: {
            width: 150,
            height: 233.4,
            offsetX: -150,
            offsetY: -1680,
        },
        card_armor_mage_feet: {
            width: 150,
            height: 233.4,
            offsetX: -300,
            offsetY: -1680,
        },
        card_armor_mage_hands: {
            width: 150,
            height: 233.4,
            offsetX: -450,
            offsetY: -1680,
        },
        card_armor_mage_head: {
            width: 150,
            height: 233.4,
            offsetX: -600,
            offsetY: -1680,
        },
        card_armor_obsidian_chest: {
            width: 150,
            height: 233.4,
            offsetX: -750,
            offsetY: -1680,
        },
        card_armor_obsidian_feet: {
            width: 150,
            height: 233.4,
            offsetX: -900,
            offsetY: -1680,
        },
        card_armor_obsidian_hands: {
            width: 150,
            height: 233.4,
            offsetX: -1050,
            offsetY: -1680,
        },
        card_armor_obsidian_head: {
            width: 150,
            height: 233.4,
            offsetX: -1200,
            offsetY: -1680,
        },
        card_armor_plate_chest: {
            width: 150,
            height: 233.4,
            offsetX: -1350,
            offsetY: -1680,
        },
        card_armor_plate_feet: {
            width: 150,
            height: 233.4,
            offsetX: -1500,
            offsetY: -1680,
        },
        card_armor_plate_hands: {
            width: 150,
            height: 233.4,
            offsetX: -1650,
            offsetY: -1680,
        },
        card_armor_plate_head: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -1680,
        },
        card_armor_scale_chest: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: 0,
        },
        card_armor_scale_feet: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -233.4,
        },
        card_armor_scale_hands: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -466.8,
        },
        card_armor_scale_head: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -700.2,
        },
        card_attr_cha_1: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -933.6,
        },
        card_attr_cha_2: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -1167,
        },
        card_attr_con_1: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -1400.4,
        },
        card_attr_con_2: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -1633.8,
        },
        card_attr_dex_1: { width: 150, height: 233.4, offsetX: -2100, offsetY: 0 },
        card_attr_dex_2: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -233.4,
        },
        card_attr_int_1: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -466.8,
        },
        card_attr_int_2: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -700.2,
        },
        card_attr_str_1: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -933.6,
        },
        card_attr_str_2: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -1167,
        },
        card_attr_wis_1: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -1400.4,
        },
        card_attr_wis_2: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -1633.8,
        },
        card_back: { width: 150, height: 233.4, offsetX: 0, offsetY: -1913.4 },
        card_class_alchemist: {
            width: 150,
            height: 233.4,
            offsetX: -150,
            offsetY: -1913.4,
        },
        card_class_artificer: {
            width: 150,
            height: 233.4,
            offsetX: -300,
            offsetY: -1913.4,
        },
        card_class_barbarian: {
            width: 150,
            height: 233.4,
            offsetX: -450,
            offsetY: -1913.4,
        },
        card_class_bard: {
            width: 150,
            height: 233.4,
            offsetX: -600,
            offsetY: -1913.4,
        },
        card_class_cleric: {
            width: 150,
            height: 233.4,
            offsetX: -750,
            offsetY: -1913.4,
        },
        card_class_druid: {
            width: 150,
            height: 233.4,
            offsetX: -900,
            offsetY: -1913.4,
        },
        card_class_fighter: {
            width: 150,
            height: 233.4,
            offsetX: -1050,
            offsetY: -1913.4,
        },
        card_class_merchant: {
            width: 150,
            height: 233.4,
            offsetX: -1200,
            offsetY: -1913.4,
        },
        card_class_monk: {
            width: 150,
            height: 233.4,
            offsetX: -1350,
            offsetY: -1913.4,
        },
        card_class_necromancer: {
            width: 150,
            height: 233.4,
            offsetX: -1500,
            offsetY: -1913.4,
        },
        card_class_paladin: {
            width: 150,
            height: 233.4,
            offsetX: -1650,
            offsetY: -1913.4,
        },
        card_class_ranger: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -1913.4,
        },
        card_class_rogue: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -1913.4,
        },
        card_class_wizard: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -1913.4,
        },
        card_exp: { width: 150, height: 233.4, offsetX: -2250, offsetY: 0 },
        card_grit: { width: 150, height: 233.4, offsetX: -2250, offsetY: -233.4 },
        card_item_1: { width: 150, height: 233.4, offsetX: -2250, offsetY: -466.8 },
        card_item_10: {
            width: 150,
            height: 233.4,
            offsetX: -2250,
            offsetY: -700.2,
        },
        card_item_11: {
            width: 150,
            height: 233.4,
            offsetX: -2250,
            offsetY: -933.6,
        },
        card_item_12: { width: 150, height: 233.4, offsetX: -2250, offsetY: -1167 },
        card_item_13: {
            width: 150,
            height: 233.4,
            offsetX: -2250,
            offsetY: -1400.4,
        },
        card_item_14: {
            width: 150,
            height: 233.4,
            offsetX: -2250,
            offsetY: -1633.8,
        },
        card_item_15: {
            width: 150,
            height: 233.4,
            offsetX: -2250,
            offsetY: -1867.2,
        },
        card_item_16: { width: 150, height: 233.4, offsetX: 0, offsetY: -2146.8 },
        card_item_17: {
            width: 150,
            height: 233.4,
            offsetX: -150,
            offsetY: -2146.8,
        },
        card_item_18: {
            width: 150,
            height: 233.4,
            offsetX: -300,
            offsetY: -2146.8,
        },
        card_item_19: {
            width: 150,
            height: 233.4,
            offsetX: -450,
            offsetY: -2146.8,
        },
        card_item_2: { width: 150, height: 233.4, offsetX: -600, offsetY: -2146.8 },
        card_item_20: {
            width: 150,
            height: 233.4,
            offsetX: -750,
            offsetY: -2146.8,
        },
        card_item_21: {
            width: 150,
            height: 233.4,
            offsetX: -900,
            offsetY: -2146.8,
        },
        card_item_3: {
            width: 150,
            height: 233.4,
            offsetX: -1050,
            offsetY: -2146.8,
        },
        card_item_4: {
            width: 150,
            height: 233.4,
            offsetX: -1200,
            offsetY: -2146.8,
        },
        card_item_5: {
            width: 150,
            height: 233.4,
            offsetX: -1350,
            offsetY: -2146.8,
        },
        card_item_6: {
            width: 150,
            height: 233.4,
            offsetX: -1500,
            offsetY: -2146.8,
        },
        card_item_7: {
            width: 150,
            height: 233.4,
            offsetX: -1650,
            offsetY: -2146.8,
        },
        card_item_8: {
            width: 150,
            height: 233.4,
            offsetX: -1800,
            offsetY: -2146.8,
        },
        card_item_9: {
            width: 150,
            height: 233.4,
            offsetX: -1950,
            offsetY: -2146.8,
        },
        card_race_dwarf: {
            width: 150,
            height: 233.4,
            offsetX: -2100,
            offsetY: -2146.8,
        },
        card_race_elf: {
            width: 150,
            height: 233.4,
            offsetX: -2250,
            offsetY: -2146.8,
        },
        card_race_fairy: { width: 150, height: 233.4, offsetX: -2400, offsetY: 0 },
        card_race_gnome: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -233.4,
        },
        card_race_goblin: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -466.8,
        },
        card_race_human: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -700.2,
        },
        card_race_orc: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -933.6,
        },
        card_threat_1: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -1167,
        },
        card_threat_10: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -1400.4,
        },
        card_threat_11: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -1633.8,
        },
        card_threat_12: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -1867.2,
        },
        card_threat_13: {
            width: 150,
            height: 233.4,
            offsetX: -2400,
            offsetY: -2100.6,
        },
        card_threat_14: { width: 150, height: 233.4, offsetX: -2550, offsetY: 0 },
        card_threat_15: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -233.4,
        },
        card_threat_16: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -466.8,
        },
        card_threat_17: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -700.2,
        },
        card_threat_18: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -933.6,
        },
        card_threat_19: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -1167,
        },
        card_threat_2: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -1400.4,
        },
        card_threat_20: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -1633.8,
        },
        card_threat_21: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -1867.2,
        },
        card_threat_3: {
            width: 150,
            height: 233.4,
            offsetX: -2550,
            offsetY: -2100.6,
        },
        card_threat_4: { width: 150, height: 233.4, offsetX: 0, offsetY: -2380.2 },
        card_threat_5: {
            width: 150,
            height: 233.4,
            offsetX: -150,
            offsetY: -2380.2,
        },
        card_threat_6: {
            width: 150,
            height: 233.4,
            offsetX: -300,
            offsetY: -2380.2,
        },
        card_threat_7: {
            width: 150,
            height: 233.4,
            offsetX: -450,
            offsetY: -2380.2,
        },
        card_threat_8: {
            width: 150,
            height: 233.4,
            offsetX: -600,
            offsetY: -2380.2,
        },
        card_threat_9: {
            width: 150,
            height: 233.4,
            offsetX: -750,
            offsetY: -2380.2,
        },
        card_threat_back: {
            width: 150,
            height: 233.4,
            offsetX: -900,
            offsetY: -2380.2,
        },
        card_threat_vacant: {
            width: 150,
            height: 233.4,
            offsetX: -1050,
            offsetY: -2380.2,
        },
        setloc_active_docks: { width: 450, height: 210, offsetX: 0, offsetY: 0 },
        setloc_ancient_tower: {
            width: 450,
            height: 210,
            offsetX: 0,
            offsetY: -210,
        },
        setloc_back: { width: 450, height: 210, offsetX: -450, offsetY: 0 },
        setloc_barren_wasteland: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -210,
        },
        setloc_battling_coliseum: {
            width: 450,
            height: 210,
            offsetX: 0,
            offsetY: -420,
        },
        setloc_capable_cabin: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -420,
        },
        setloc_corrupted_laboratory: {
            width: 450,
            height: 210,
            offsetX: 0,
            offsetY: -630,
        },
        setloc_crowded_market: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -630,
        },
        setloc_dragons_lair: { width: 450, height: 210, offsetX: -900, offsetY: 0 },
        setloc_eerie_prison: {
            width: 450,
            height: 210,
            offsetX: -900,
            offsetY: -210,
        },
        setloc_equipped_dungeon: {
            width: 450,
            height: 210,
            offsetX: -900,
            offsetY: -420,
        },
        setloc_frozen_tundra: {
            width: 450,
            height: 210,
            offsetX: -900,
            offsetY: -630,
        },
        setloc_gambling_alley: {
            width: 450,
            height: 210,
            offsetX: 0,
            offsetY: -840,
        },
        setloc_ghostly_crypt: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -840,
        },
        setloc_hidden_cave: {
            width: 450,
            height: 210,
            offsetX: -900,
            offsetY: -840,
        },
        setloc_holy_temple: { width: 450, height: 210, offsetX: 0, offsetY: -1050 },
        setloc_lively_city: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -1050,
        },
        setloc_magical_portal: {
            width: 450,
            height: 210,
            offsetX: -900,
            offsetY: -1050,
        },
        setloc_nonexistent_void: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: 0,
        },
        setloc_overgrown_forest: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: -210,
        },
        setloc_peaceful_river: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: -420,
        },
        setloc_quiet_library: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: -630,
        },
        setloc_secret_garden: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: -840,
        },
        setloc_sheltered_stables: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: -1050,
        },
        setloc_sinister_underworld: {
            width: 450,
            height: 210,
            offsetX: 0,
            offsetY: -1260,
        },
        setloc_starved_labyrinth: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -1260,
        },
        setloc_transcendent_observatory: {
            width: 450,
            height: 210,
            offsetX: -900,
            offsetY: -1260,
        },
        setloc_traveling_caravan: {
            width: 450,
            height: 210,
            offsetX: -1350,
            offsetY: -1260,
        },
        setloc_treacherous_tunnels: {
            width: 450,
            height: 210,
            offsetX: 0,
            offsetY: -1470,
        },
        setloc_unruly_tavern: {
            width: 450,
            height: 210,
            offsetX: -450,
            offsetY: -1470,
        },
    };
    return StaticDataSprites;
}());
/*
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Effortless implementation : © Kevin Kelley <kelleyk@kelleyk.net>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */
/// <amd-module name="bgagame/effortless"/>
// import Gamegui = require('ebg/core/gamegui');
// import 'ebg/counter';
/** The root for all of your game code. */
var GameBody = /** @class */ (function (_super) {
    __extends(GameBody, _super);
    /** @gameSpecific See {@link Gamegui} for more information. */
    function GameBody() {
        var _this = _super.call(this) || this;
        // myGlobalValue: number = 0;
        // myGlobalArray: string[] = [];
        // These are cached copies of the last instance of each of these update messages the client has seen.
        _this.mutableBoardState = null;
        _this.privateState = null;
        _this.tablewidePanelEl = undefined;
        _this.inputArgs = null;
        _this.selectedLocation = null;
        _this.selectedCard = null;
        _this.selectedEffortPile = null;
        // XXX: Do we not have a good type for `ebg.zone`?
        _this.handZone = null;
        _this.locationZones = {};
        // Maps position (sublocationIndex) to location ID.
        _this.locationByPos = {};
        console.log('effortless constructor');
        _this.cardZoneObserver = new MutationObserver(function (records, _observer) {
            for (var _i = 0, records_1 = records; _i < records_1.length; _i++) {
                var record = records_1[_i];
                // console.log('mutation observed:', records, observer);
                for (var _a = 0, _b = Array.from(record.addedNodes); _a < _b.length; _a++) {
                    var _el = _b[_a];
                    var el = _el;
                    _this.onCardAddedToZone(el, _this.getCardState(_this.cardIdFromElId(el.id)));
                }
            }
        });
        return _this;
    }
    /** @gameSpecific See {@link Gamegui.setup} for more information. */
    GameBody.prototype.setup = function (gamedatas) {
        console.log('*** Entering `setup()`; gamedatas=', gamedatas);
        // XXX: Where should this go?
        //
        // XXX: Also, Zone is not responsive.
        var handZoneEl = document.querySelector('#ewc_handarea #ewc_handarea_zone');
        this.handZone = new ebg.zone();
        this.setUpCardZone(this.handZone, handZoneEl);
        // Setting up player boards
        for (var playerId in gamedatas.players) {
            if (gamedatas.players.hasOwnProperty(playerId)) {
                var player = gamedatas.players[playerId];
                // TODO: Setting up players boards if needed
                console.log(player);
            }
        }
        // TODO: Set up your game interface here, according to "gamedatas"
        this.setupSeatBoards(gamedatas.mutableBoardState);
        this.setupPlayArea(gamedatas.mutableBoardState);
        // Setup game notifications to handle (see "setupNotifications" method below)
        this.setupNotifications();
        for (var _i = 0, _a = this.allCardZones(); _i < _a.length; _i++) {
            var z = _a[_i];
            this.cardZoneObserver.observe(z.container_div, { childList: true });
        }
        console.log('Ending game setup');
    };
    // XXX: Needs to be renamed.
    GameBody.prototype.onCardAddedToZone = function (el, card) {
        var cardMetadata = !card.visible
            ? null
            : StaticDataCards.cardMetadata[card.cardType];
        if (cardMetadata !== null) {
            console.log('  metadata found; adding tooltip: ', el.id, cardMetadata);
            this.addTooltipHtml(el.id, this.format_block('jstpl_tooltip_card', cardMetadata));
        }
        else {
            this.removeTooltip(el.id);
        }
    };
    // This overrides a default implementation that BGA provides.
    GameBody.prototype.updatePlayerOrdering = function () {
        console.log('*** updatePlayerOrdering()');
        // N.B.: We start at 1 instead of at 0, like the default implementation does, so that our table-wide panel stays at
        // the top.  We should eventually improve this so that it handles seat boards as well.
        var place = 1;
        for (var _i = 0, _a = Object.keys(this.gamedatas.playerorder); _i < _a.length; _i++) {
            var i = _a[_i];
            var playerId = this.gamedatas.playerorder[i];
            dojo.place('overall_player_board_' + playerId, 'player_boards', place);
            place++;
        }
    };
    GameBody.prototype.setupSeatBoards = function (mutableBoardState) {
        console.log('setupSeatBoards(): #player_boards =', $('player_boards').children.length);
        this.tablewidePanelEl = dojo.place(this.format_block('jstpl_tablewide_panel', {}), $('player_boards'), 'first');
        // console.log(
        //   'setupSeatBoards(): after tablewide panel creation, player_boards =',
        //   $('player_boards').children,
        // );
        var reservePiles = {};
        for (var _i = 0, _a = Object.values(mutableBoardState.effortPiles); _i < _a.length; _i++) {
            var pile = _a[_i];
            if (pile.locationId === null) {
                reservePiles[pile.seatId] = pile;
            }
        }
        for (var _b = 0, _c = Object.values(mutableBoardState.seats); _b < _c.length; _b++) {
            var seat = _c[_b];
            if (seat.playerId === null) {
                dojo.place(this.format_block('jstpl_seat_board', {
                    seatColor: seat.seatColor,
                    seatId: seat.id,
                    seatLabel: seat.seatLabel,
                }), $('player_boards'));
            }
            var seatBoardEl = (seat.playerId === null
                ? document.querySelector('#overall_seat_board_' + seat.id + ' .player_panel_content')
                : document.querySelector('#overall_player_board_' +
                    seat.playerId +
                    ' .player_panel_content'));
            // XXX: Need to find actual values for these params.
            dojo.place(this.format_block('jstpl_seat_board_contents', {
                colorName: seat.colorName,
                reservePileId: reservePiles[seat.id].id,
            }), seatBoardEl);
        }
        // Hide scores, since we don't award points until the scoring phase at the end of the game.
        document.querySelectorAll('.player_score').forEach(function (el) {
            el.style.visibility = 'hidden';
        });
    };
    GameBody.prototype.setupPlayArea = function (mutableBoardState) {
        var _this = this;
        for (var _i = 0, _a = Object.values(mutableBoardState.locations); _i < _a.length; _i++) {
            var location_1 = _a[_i];
            this.locationByPos[location_1.sublocationIndex] = location_1;
        }
        var _loop_1 = function (i) {
            var el = dojo.place(this_1.format_block('jstpl_setloc_panel', {
                classes: 'ewc_setloc_location_' + this_1.locationByPos[i].id,
                id: 'ewc_setloc_panel_' + i,
            }), $('ewc_setlocarea_column_' + (i % 2)));
            dojo.connect(el.querySelector('.ewc_setloc_setloc_wrap'), 'onclick', this_1, function (evt) {
                _this.onClickLocation(evt, _this.locationByPos[i].id);
            });
        };
        var this_1 = this;
        // Create the element that will display each setting-location pair and associated cards.
        for (var i = 0; i < 6; ++i) {
            _loop_1(i);
        }
        for (var _b = 0, _c = Object.values(mutableBoardState.locations); _b < _c.length; _b++) {
            var location_2 = _c[_b];
            console.log('*** location', location_2);
            var parentEl = document.querySelector('#ewc_setloc_panel_' + location_2.sublocationIndex + ' .ewc_setloc_cards');
            var zone = new ebg.zone();
            this.setUpCardZone(zone, parentEl);
            this.locationZones[location_2.id] = zone;
        }
        var _loop_2 = function (pile) {
            // Ignore reserve piles.
            if (pile.locationId !== null) {
                var seat = mutableBoardState.seats[pile.seatId];
                var location_3 = mutableBoardState.locations[pile.locationId];
                var parentEl = document.querySelector('#ewc_setloc_panel_' +
                    location_3.sublocationIndex +
                    ' .ewc_effort_counter_wrap');
                var el = dojo.place(this_2.format_block('jstpl_effort_counter', {
                    colorName: seat.colorName,
                    id: pile.id,
                }), parentEl);
                dojo.connect(el, 'onclick', this_2, function (evt) {
                    _this.onClickEffortPile(evt, pile.id);
                });
            }
        };
        var this_2 = this;
        // Create a counter for the amount of effort that each seat has on each location.
        for (var _d = 0, _e = Object.values(mutableBoardState.effortPiles); _d < _e.length; _d++) {
            var pile = _e[_d];
            _loop_2(pile);
        }
        this.applyState(mutableBoardState, /*privateState=*/ null);
    };
    GameBody.prototype.setUpCardZone = function (zone, el) {
        // XXX: This is hardwired for now, but it should be the scaled size of these sprites.
        zone.create(this, el, 50, 185);
        zone.setPattern('custom');
        zone.itemIdToCoords = function (i, controlWidth) {
            // XXX: This spacing should probably be a percentage so that it's scaling-independent; what we care about is how
            // much of the card image we must show.
            var spacing = 35;
            var offset = (controlWidth - (zone.item_width + spacing * (zone.items.length - 1))) /
                2;
            return {
                h: zone.item_height,
                w: zone.item_width,
                x: offset + spacing * i,
                y: 0,
            };
        };
    };
    // This is the top-level state update function.  It's called each time we get an update from the server that includes
    // one or more of the state update messages (public and private) that the server generates.
    //
    // The parameters are optional because not every update includes all of these messages.  If a parameter isn't given,
    // we use the last copy of that message we've seen.
    //
    // Types of mutable state updates supported:
    // - cards entering and leaving the play area - TODO: needs to be animated as cards moving to/from discard & deck
    //
    // TODO: Types of state updates not yet supported, but that we'll need:
    // - update effort-pile counters
    // - cards moving from setloc to hand
    // - discarding and replacing a setloc
    // - flipping a card at a setloc in place
    // - a card flipping when a face-down card is drawn from a location
    // - cards moving from hand to setloc (?)
    // - cards moving from hand to discard
    //
    GameBody.prototype.applyState = function (mutableBoardState, privateState) {
        var _this = this;
        if (mutableBoardState === null || mutableBoardState === undefined) {
            mutableBoardState = this.mutableBoardState;
            console.log('applyState(): using cached mutableBoardState');
        }
        else {
            this.mutableBoardState = mutableBoardState;
            console.log('applyState(): using novel mutableBoardState');
        }
        if (privateState === null || privateState === undefined) {
            privateState = this.privateState;
            console.log('applyState(): using cached privateState');
        }
        else {
            this.privateState = privateState;
            console.log('applyState(): using novel privateState');
        }
        console.log('using mutableBoardState & privateState:', mutableBoardState, privateState);
        // XXX: We should finish eliminating this legacy update routine.
        if (mutableBoardState !== null) {
            console.log('applyState(): updating mutableBoardState', mutableBoardState);
            this.applyMutableBoardState(mutableBoardState);
        }
        // -----------
        // Cards
        // -----------
        var seenCardIds = {};
        for (var _i = 0, _a = this.allCardState(); _i < _a.length; _i++) {
            var card = _a[_i];
            switch (card.sublocation) {
                case 'SETLOC': {
                    seenCardIds[card.id] = true;
                    break;
                }
                case 'HAND': {
                    seenCardIds[card.id] = true;
                    break;
                }
            }
            this.placeCard(card);
        }
        console.log('*** seenCardIds:', seenCardIds);
        // XXX: We'll need to expand this to deal with cards leaving the hand, as well.
        document.querySelectorAll('.ewc_card_playarea').forEach(function (rawEl) {
            var el = rawEl;
            var cardId = _this.cardIdFromElId(el.id);
            console.log('  - existent card ID:', el.id, cardId);
            if (!seenCardIds.hasOwnProperty(cardId)) {
                console.log('     not present in update; destroying el!');
                _this.removeFromCardZones(el, /*destroy=*/ true);
            }
        });
        // -------
        // Rescaling (and tinting eventually?)
        // -------
        this.rescaleCardSprites();
        // -------
        // Update UI elements
        // -------
        this.refreshUiElements();
    };
    GameBody.prototype.allCardState = function () {
        var cards = [];
        // XXX: should we init these things with empty messages so that we don't need all of these null checks?
        if (this.privateState !== null) {
            cards = cards.concat(Object.values(this.privateState.cards));
        }
        if (this.mutableBoardState !== null) {
            cards = cards.concat(Object.values(this.mutableBoardState.cards));
        }
        return cards;
    };
    GameBody.prototype.getCardState = function (cardId) {
        for (var _i = 0, _a = this.allCardState(); _i < _a.length; _i++) {
            var card = _a[_i];
            if (card.id === cardId) {
                return card;
            }
        }
        return null;
    };
    // This is called to let UI elements react to changing data and contents.
    GameBody.prototype.refreshUiElements = function () {
        for (var _i = 0, _a = this.allCardZones(); _i < _a.length; _i++) {
            var zone = _a[_i];
            zone.updateDisplay();
            // Fix z-order so that cards on the right are in front of cards on the left.
            var i = 0;
            for (var _b = 0, _c = zone.items; _b < _c.length; _b++) {
                var item = _c[_b];
                $(item.id).style.zIndex = '' + ++i;
            }
        }
    };
    // XXX: It'd be nice if we could eliminate the need for this.
    GameBody.prototype.cardIdFromElId = function (elId) {
        var rxp = /^cardid_(\d+)$/;
        var m = rxp.exec(elId);
        return parseInt(m[1], 10);
    };
    // XXX: We should find a way to make this more generic.
    GameBody.prototype.rescaleCardSprites = function () {
        var _this = this;
        // This function assumes that the matched element has a parent wrapper element.
        console.log('*** qsa ***');
        document
            .querySelectorAll('.tmp_scalable_card')
            .forEach(function (rawEl) {
            var el = rawEl;
            // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
            if (el.classList.contains('tmp_scaled_card')) {
                return;
            }
            el.classList.add('tmp_scaled_card');
            var scaleFactor = 0.75;
            _this.rescaleSprite(el, scaleFactor);
        });
    };
    GameBody.prototype.applyMutableBoardState = function (mutableBoardState) {
        var _this = this;
        this.mutableBoardState = mutableBoardState;
        // XXX: This will probably need a little bit of work when we start supporting changes to and discarding of settings
        // and locations.
        for (var _i = 0, _a = Object.values(mutableBoardState.locations); _i < _a.length; _i++) {
            var location_4 = _a[_i];
            console.log('*** location', location_4);
            // XXX: We need the bang ("!") here because, if the card is not visible, we won't know its type.  These particular
            // cards are always visible, however.  Should we consider improving our types so that we have visible and
            // not-visible subtypes?
            document
                .querySelector('#ewc_setloc_panel_' +
                location_4.sublocationIndex +
                ' .ewc_setloc_location')
                .classList.add(location_4.cardType.replace(':', '_'));
        }
        for (var _b = 0, _c = Object.values(mutableBoardState.effortPiles); _b < _c.length; _b++) {
            var pile = _c[_b];
            // // Ignore reserve piles.
            // if (pile.locationId !== null) {
            document.querySelector('#ewc_effort_counter_' + pile.id + ' .ewc_effort_counter_value').innerText = '' + pile.qty;
            // }
        }
        // XXX: We'll also need to update reserve piles once we draw them in player boards, of course.
        for (var _d = 0, _e = Object.values(mutableBoardState.settings); _d < _e.length; _d++) {
            var setting = _e[_d];
            // console.log('*** setting', setting);
            // XXX: See above comment about "!".
            document
                .querySelector('#ewc_setloc_panel_' +
                setting.sublocationIndex +
                ' .ewc_setloc_setting')
                .classList.add(setting.cardType.replace(':', '_'));
        }
        document
            .querySelectorAll('.tmp_scalable_cube')
            .forEach(function (rawEl) {
            var el = rawEl;
            // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
            if (el.classList.contains('tmp_scaled_cube')) {
                return;
            }
            el.classList.add('tmp_scale_cube');
            _this.rescaleSpriteCube(el, 0.6);
        });
        document.querySelectorAll('.tmp_tintable').forEach(function (rawEl) {
            var el = rawEl;
            // Don't rescale on multiple calls.  We may not need this if we're always rescaling from "source dimensions".
            if (el.classList.contains('tmp_tinted')) {
                return;
            }
            el.classList.add('tmp_tinted');
            // XXX: We have several versions of this color translation code between the client and server sides of the game.
            // We should find a better way to consolidate.
            if (el.classList.contains('ewc_playercolor_teal')) {
                _this.tintSprite(el, '#00b796');
            }
            if (el.classList.contains('ewc_playercolor_pink')) {
                _this.tintSprite(el, '#ff5fa2');
            }
            if (el.classList.contains('ewc_playercolor_blue')) {
                _this.tintSprite(el, '#001489');
            }
            if (el.classList.contains('ewc_playercolor_yellow')) {
                _this.tintSprite(el, '#ffe900');
            }
            if (el.classList.contains('ewc_playercolor_white')) {
                _this.tintSprite(el, '#ffffff');
            }
        });
    };
    // Returns all zones that can contain play-area cards.
    GameBody.prototype.allCardZones = function () {
        var zones = [this.handZone];
        zones = zones.concat(Object.values(this.locationZones));
        return zones;
    };
    // Does not re-place `elId` if it is already in `zone`; handles removing it from another zone if it belongs to one.
    //
    // (If we place an element in a second zone without removing it from the first one, the two zones will "argue" over
    // the element.)
    GameBody.prototype.placeCardInZone = function (zone, card, el) {
        if (!zone.isInZone(el.id)) {
            this.removeFromCardZones(el, /*destroy=*/ false);
            var cardMetadata = !card.visible
                ? -1
                : StaticDataCards.cardMetadata[card.cardType];
            console.log('zone el', el.id, 'sort weight', cardMetadata);
            zone.placeInZone(el.id, cardMetadata.sortWeight);
        }
    };
    // Removes the card `elId` from any zone(s) that it currently belongs to.
    //
    // If we destroy the corresponding element without removing it from a zone first, it will visually disappear, but it
    // will still be present in the zone's set of `items` and space will be left for it when the zone arranges its
    // contents.
    GameBody.prototype.removeFromCardZones = function (el, destroy) {
        for (var _i = 0, _a = this.allCardZones(); _i < _a.length; _i++) {
            var z = _a[_i];
            z.removeFromZone(el.id, /*bDestroy=*/ false);
        }
        if (destroy) {
            this.fadeOutAndDestroy(el);
        }
    };
    GameBody.prototype.placeCard = function (card) {
        switch (card.sublocation) {
            case 'SETLOC':
            case 'HAND':
                break;
            default:
                // Card not in the play-area.
                return;
        }
        console.log('*** card', card);
        // XXX: We're going to need to deal with the fact that we have (unique) instances of cards in the play area, but
        // also (potentially not unique) copies of those cards in the prompt area and in tooltips.
        var el = document.getElementById('cardid_' + card.id);
        if (el === null) {
            // This is the table-wide panel in the top right.  If the card is not already in the play-area, we will spawn it
            // here and slide it into position; if it's leaving the play area, we'll slide it over here and then destroy it.
            var spawnEl = document.getElementById('tablewide_panel');
            var cardType = !card.visible ? 'back' : card.cardType;
            el = dojo.place(this.format_block('jstpl_playarea_card', {
                cardType: cardType,
                id: card.id,
            }), spawnEl);
        }
        switch (card.sublocation) {
            case 'SETLOC': {
                console.log('  - in setloc');
                var location_5 = this.locationByPos[card.sublocationIndex];
                this.placeCardInZone(this.locationZones[location_5.id], card, el);
                break;
            }
            case 'HAND': {
                console.log('  - in hand');
                this.placeCardInZone(this.handZone, card, el);
                break;
            }
            default: {
                console.log('  - other sublocation: ' + card.sublocation);
                break;
            }
        }
        // XXX: When a card is moved from a setloc zone to the hand zone, the tooltip stops working.
        {
            // Handle appearance changes (e.g. when the card flips over).
            var cardType = !card.visible ? 'back' : card.cardType;
            if (!el.classList.contains('card_' + cardType)) {
                for (var _i = 0, _a = Array.from(el.classList).filter(function (x) {
                    return x.match(/^card_/);
                }); _i < _a.length; _i++) {
                    var className = _a[_i];
                    el.classList.remove(className);
                }
                el.classList.add('card_' + cardType);
                // N.B.: This is necessary in order to recalculate all of the sprite-sheet offsets; without it, the appearance
                // of the card won't actually change, even though we've replaced the "card_*" CSS class.
                this.rescaleSprite(el, 0.5);
            }
            this.onCardAddedToZone(el, card);
        }
    };
    // XXX: The need for this is a bit unfortunate; we could eliminate it.
    GameBody.prototype.getSpriteName = function (el) {
        // console.log('*** getSpriteName()', el.classList);
        for (var _i = 0, _a = Object.values(el.classList); _i < _a.length; _i++) {
            var className = _a[_i];
            console.log(className);
            if (className.match(/^card_/g)) {
                return className;
            }
        }
        throw new Error('XXX: Unable to find sprite name.');
    };
    GameBody.prototype.rescaleSprite = function (el, scale) {
        var spriteName = this.getSpriteName(el);
        var spriteMetadata = StaticDataSprites.spriteMetadata[spriteName];
        // console.log('rescaleSprite for spriteName=', spriteName);
        // XXX: We should pull these numbers from static card data.
        el.style.height = spriteMetadata.height * scale + 'px';
        el.style.width = spriteMetadata.width * scale + 'px';
        var bgSize = StaticDataSprites.totalWidth * scale +
            'px ' +
            StaticDataSprites.totalHeight * scale +
            'px';
        // console.log('*** bgSize = ', bgSize);
        el.style.backgroundSize = bgSize;
        el.style.backgroundPosition =
            spriteMetadata.offsetX * scale +
                'px ' +
                spriteMetadata.offsetY * scale +
                'px';
    };
    GameBody.prototype.rescaleSpriteCube = function (el, scale) {
        // console.log('** rescaleSpriteCube()', el, scale);
        el.style.height = 30.0 * scale + 'px';
        el.style.width = 30.0 * scale + 'px';
        var spritesheetSize = 312.0 * scale + 'px ' + 302.4 * scale + 'px';
        // console.log('*** bgSize = ', spritesheetSize);
        el.style.backgroundSize = spritesheetSize;
        el.style.maskSize = spritesheetSize;
        var spritesheetPos = -276.0 * scale + 'px ' + -121.2 * scale + 'px';
        el.style.backgroundPosition = spritesheetPos;
        el.style.maskPosition = spritesheetPos;
    };
    GameBody.prototype.tintSprite = function (el, color) {
        el.style.backgroundBlendMode = 'multiply';
        el.style.backgroundColor = color;
    };
    ///////////////////////////////////////////////////
    //// Log-message formatting
    /** @override */
    //
    // This override repeatedly substitutes arguments until the string does not change.  This is useful for situations
    // such as our ST_INPUT, where some of the values in `args` contain substitution patterns themselves.
    GameBody.prototype.format_string_recursive = function (log, args) {
        var lastLog;
        do {
            lastLog = log;
            log = this.inherited(this.format_string_recursive, [log, args]);
        } while (log !== lastLog);
        return this.replaceLogEntities(log);
    };
    // XXX: This is a short-term stand-in.
    //
    // TODO: For entityTypes of location, setting we should create tooltips.
    GameBody.prototype.replaceLogEntities = function (log) {
        return log.replace(/:([a-z0-9-_]+)=([a-z0-9-_]+?):/g, function (_m, _entityType, entityName) {
            console.log('match parts: ', _m, _entityType, entityName);
            return entityName.charAt(0).toUpperCase() + entityName.slice(1);
        });
    };
    ///////////////////////////////////////////////////
    //// User input
    // XXX: Now that we have `this.inputArgs`, should we always use that?
    GameBody.prototype.updateSelectables = function (inputArgs) {
        var _this = this;
        console.log('*** updateSelectables()');
        document.querySelectorAll('.ewc_selectable').forEach(function (el) {
            el.classList.remove('ewc_selectable');
        });
        document.querySelectorAll('.ewc_unselectable').forEach(function (el) {
            el.classList.remove('ewc_unselectable');
        });
        document.querySelectorAll('.ewc_selected').forEach(function (el) {
            el.classList.remove('ewc_selected');
        });
        switch (inputArgs.inputType) {
            case 'inputtype:location': {
                console.log('  *** inputtype:location');
                for (var _i = 0, _a = inputArgs.choices; _i < _a.length; _i++) {
                    var id = _a[_i];
                    document
                        .querySelector('.ewc_setloc_location_' + id + ' .ewc_setloc_setloc_wrap')
                        .classList.add('ewc_selectable');
                }
                document
                    .querySelectorAll('.ewc_setloc_setloc_wrap:not(.ewc_selectable)')
                    .forEach(function (el) {
                    el.classList.add('ewc_unselectable');
                });
                break;
            }
            case 'inputtype:card': {
                console.log('  *** inputtype:card', inputArgs);
                this.placeAndWipeIn(this.format_block('jstpl_promptarea', {}), 'ewc_promptarea_wrap');
                var _loop_3 = function (_card) {
                    // XXX: Hacky; we should instead fix our type definitions.
                    var card = _card;
                    var cardType = !card.visible ? 'back' : card.cardType;
                    var parentEl = document.querySelector('.ewc_promptarea .ewc_promptarea_choices');
                    // XXX: We also need to make these .ewc_selectable; and we're going to wind up needing to do other stuff such
                    // as attaching tooltips and on-click handlers.  We should move this into a reusable function.
                    var el = dojo.place(this_3.format_block('jstpl_prompt_card', {
                        cardType: cardType,
                        id: card.id,
                    }), parentEl);
                    this_3.rescaleSprite(el, 0.35);
                    el.classList.add('ewc_selectable');
                    dojo.connect(el, 'onclick', this_3, function (evt) {
                        _this.onClickCard(evt, card.id);
                    });
                };
                var this_3 = this;
                for (var _b = 0, _c = Object.values(inputArgs.choices); _b < _c.length; _b++) {
                    var _card = _c[_b];
                    _loop_3(_card);
                }
                // for (const id of inputArgs.choices) {
                //   document
                //     .querySelector(
                //       '.ewc_setloc_location_' + id + ' .ewc_setloc_setloc_wrap',
                //     )!
                //     .classList.add('ewc_selectable');
                // }
                break;
            }
            case 'inputtype:effort-pile': {
                // N.B.: The `EffortPile` type on the server can represent either a reserve effort pile or an effort pile on a
                // location.  When asked for input, though, we'll only be picking the latter type.
                console.log('  *** inputtype:effort-pile');
                for (var _d = 0, _e = inputArgs.choices; _d < _e.length; _d++) {
                    var id = _e[_d];
                    // XXX: Should we use classes rather than IDs here for consistency with other things?
                    document
                        .querySelector('#ewc_effort_counter_' + id)
                        .classList.add('ewc_selectable');
                }
                document
                    .querySelectorAll('.ewc_effort_counter:not(.ewc_selectable)')
                    .forEach(function (el) {
                    el.classList.add('ewc_unselectable');
                });
                break;
            }
            default: {
                throw new Error('Unexpected input type: ' + inputArgs.inputType);
            }
        }
    };
    // XXX: Pick better type than `any`
    //
    // XXX: Does this also need to check that the target is .ewc_selectable?
    GameBody.prototype.onClickLocation = function (evt, locationId) {
        console.log('onClickLocation', evt);
        if (this.inputArgs === null ||
            this.inputArgs.inputType !== 'inputtype:location' ||
            !this.inputArgs.choices.includes(locationId)) {
            return;
        }
        document.querySelectorAll('.ewc_selected').forEach(function (el) {
            el.classList.remove('ewc_selected');
        });
        evt.currentTarget.classList.add('ewc_selected');
        this.selectedLocation = locationId;
        this.triggerUpdateActionButtons();
    };
    // XXX: Pick better type than `any`
    //
    // XXX: Does this also need to check that the target is .ewc_selectable?
    GameBody.prototype.onClickEffortPile = function (evt, pileId) {
        console.log('onClickEffortPile', evt);
        console.log('  clicked pile = ' + pileId + '; choices = ', this.inputArgs.choices);
        if (this.inputArgs === null ||
            this.inputArgs.inputType !== 'inputtype:effort-pile' ||
            !this.inputArgs.choices.includes(pileId)) {
            return;
        }
        document.querySelectorAll('.ewc_selected').forEach(function (el) {
            el.classList.remove('ewc_selected');
        });
        evt.currentTarget.classList.add('ewc_selected');
        this.selectedEffortPile = pileId;
        this.triggerUpdateActionButtons();
    };
    // XXX: Pick better type than `any`
    //
    // XXX: Does this also need to check that the target is .ewc_selectable?
    GameBody.prototype.onClickCard = function (evt, cardId) {
        console.log('onClickCard', evt);
        // XXX: We could get rid of the weirdly-different-ness of the card input type if we made `choices` an array of ints
        // and then had a separate place to put the full metadata about the cards.
        if (this.inputArgs === null ||
            this.inputArgs.inputType !== 'inputtype:card') {
            return;
        }
        var choiceIds = this.inputArgs.choices.map(function (card) {
            return card.id;
        });
        if (!choiceIds.includes(cardId)) {
            return;
        }
        document.querySelectorAll('.ewc_selected').forEach(function (el) {
            el.classList.remove('ewc_selected');
        });
        evt.currentTarget.classList.add('ewc_selected');
        this.selectedCard = cardId;
        this.triggerUpdateActionButtons();
    };
    ///////////////////////////////////////////////////
    //// Utils
    // public locationIdFromElId(elId: string): number {
    //   const m = elId.match(/(\d+)$/)!;
    //   return parseInt(m[1], 10);
    // }
    ///////////////////////////////////////////////////
    //// Game & client states
    GameBody.prototype.onEnteringState = function (stateName, args) {
        console.log('Entering state', stateName, args);
        _super.prototype.onEnteringState.call(this, stateName, args);
        if (args !== null && args.args !== null) {
            this.applyState(args.args.mutableBoardState, args.args._private);
        }
        switch (stateName) {
            case 'stInput': {
                if (this.isCurrentPlayerActive()) {
                    console.log('*** stInput: ', args);
                    this.inputArgs = args.args.input;
                    this.updateSelectables(args.args.input);
                }
                break;
            }
            case 'stPostScoring': {
                console.log('*** stPostScoring: ', args);
                // // XXX: make this clear all selectablse
                // this.inputArgs = null;
                // this.updateSelectables(null);
                break;
            }
        }
    };
    /** @gameSpecific See {@link Gamegui.onLeavingState} for more information. */
    GameBody.prototype.onLeavingState = function (stateName) {
        var _this = this;
        console.log('Leaving state: ' + stateName);
        _super.prototype.onLeavingState.call(this, stateName);
        this.inputArgs = null;
        document.querySelectorAll('.ewc_promptarea').forEach(function (el) {
            _this.wipeOutAndDestroy(el);
        });
        switch (stateName) {
            case 'stInput': {
                break;
            }
        }
    };
    GameBody.prototype.onUpdateActionButtons = function (stateName, args) {
        var _this = this;
        console.log('onUpdateActionButtons()', stateName, args);
        if (!this.isCurrentPlayerActive()) {
            return;
        }
        switch (stateName) {
            case 'stInput': {
                this.addActionButton('btn_input_confirm', _('Confirm'), function () {
                    // XXX: This will only work for inputtype:location; we'll need to generalize it for other input types.
                    var rpcParam = null;
                    switch (_this.inputArgs.inputType) {
                        case 'inputtype:location': {
                            rpcParam = {
                                selection: JSON.stringify({
                                    inputType: 'inputtype:location',
                                    value: _this.selectedLocation,
                                }),
                            };
                            break;
                        }
                        case 'inputtype:card': {
                            rpcParam = {
                                selection: JSON.stringify({
                                    inputType: 'inputtype:card',
                                    value: _this.selectedCard,
                                }),
                            };
                            break;
                        }
                        case 'inputtype:effort-pile': {
                            rpcParam = {
                                selection: JSON.stringify({
                                    inputType: 'inputtype:effort-pile',
                                    value: _this.selectedEffortPile,
                                }),
                            };
                            break;
                        }
                        default: {
                            throw new Error('Unexpected input type.');
                        }
                    }
                    console.log('confirmed!', rpcParam);
                    _this.ajaxCallWrapper('actSelectInput', rpcParam);
                }, undefined, undefined, 'blue');
                var confirmReady = false;
                if (this.inputArgs !== null) {
                    switch (this.inputArgs.inputType) {
                        case 'inputtype:location': {
                            confirmReady = this.selectedLocation !== null;
                            break;
                        }
                        case 'inputtype:card': {
                            confirmReady = this.selectedCard !== null;
                            break;
                        }
                        case 'inputtype:effort-pile': {
                            confirmReady = this.selectedEffortPile !== null;
                            break;
                        }
                        default: {
                            throw new Error('Unexpected input type.');
                        }
                    }
                }
                if (!confirmReady) {
                    dojo.addClass('btn_input_confirm', 'disabled');
                }
                break;
            }
        }
    };
    ///////////////////////////////////////////////////
    //// Utility methods
    /*
          Here, you can defines some utility methods that you can use everywhere in your typescript
          script.
      */
    ///////////////////////////////////////////////////
    //// Player's action
    /*
          Here, you are defining methods to handle player's action (ex: results of mouse click on game objects).
  
          Most of the time, these methods:
          - check the action is possible at this game state.
          - make a call to the game server
      */
    /*
      Example:
      onMyMethodToCall1( evt: Event )
      {
          console.log( 'onMyMethodToCall1' );
  
          // Preventing default browser reaction
          evt.preventDefault();
  
          //	With base Gamegui class...
  
          // Check that this action is possible (see "possibleactions" in states.inc.php)
          if(!this.checkAction( 'myAction' ))
              return;
  
          this.ajaxcall( "/yourgamename/yourgamename/myAction.html", {
              lock: true,
              myArgument1: arg1,
              myArgument2: arg2,
          }, this, function( result ) {
              // What to do after the server call if it succeeded
              // (most of the time: nothing)
          }, function( is_error) {
  
              // What to do after the server call in anyway (success or failure)
              // (most of the time: nothing)
          } );
  
  
          //	With GameguiCookbook::Common...
          this.ajaxAction( 'myAction', { myArgument1: arg1, myArgument2: arg2 }, (is_error) => {} );
      }
      */
    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications
    /** @gameSpecific See {@link Gamegui.setupNotifications} for more information. */
    GameBody.prototype.setupNotifications = function () {
        console.log('notifications subscriptions setup');
        // TODO: here, associate your game notifications with local methods
        // With base Gamegui class...
        // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
        // With GameguiCookbook::Common class...
        // this.subscribeNotif( 'cardPlayed', this.notif_cardPlayed ); // Adds type safety to the subscription
    };
    return GameBody;
}(GameBasics));
// // The global 'bgagame.effortless' class is instantiated when the page is loaded. The following code sets this
// // variable to your game class.
// dojo.setObject('bgagame.effortless', Effortless);
// Same as:
// (window.bgagame ??= {}).effortless = Effortless;
console.log('*** top of GameEntrypoint.ts');
define([
    'dojo',
    'dojo/_base/declare',
    'ebg/core/gamegui',
    'ebg/counter',
    'ebg/stock',
    'ebg/zone',
], function (_dojo, declare) {
    // console.log('*** define() in GameEntrypoint.ts');
    // const x = declare(ebg.core.gamegui, new GameBody());
    // ((window as any).bgagame ??= {}).effortless = x;
    // return x;
    return declare('bgagame.effortless', ebg.core.gamegui, new GameBody());
});
//# sourceMappingURL=effortless.js.map