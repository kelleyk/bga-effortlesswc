/*! bga-effortlesswc 2024-07-15 */
var __extends=this&&this.__extends||function(){var i=function(t,e){return(i=Object.setPrototypeOf||({__proto__:[]}instanceof Array?function(t,e){t.__proto__=e}:function(t,e){for(var o in e)Object.prototype.hasOwnProperty.call(e,o)&&(t[o]=e[o])}))(t,e)};return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Class extends value "+String(e)+" is not a constructor or null");function o(){this.constructor=t}i(t,e),t.prototype=null===e?Object.create(e):(o.prototype=e.prototype,new o)}}(),GameBasics=function(e){function t(){var t=e.call(this)||this;return t.curstate=null,t.pendingUpdate=!1,t.currentPlayerWasActive=!1,console.log("(BASICS) game constructor"),t}return __extends(t,e),t.prototype.setup=function(t){console.log("(BASICS) Starting game setup"),this.gamedatas=t},t.prototype.onEnteringState=function(t,e){this.curstate=t,e=e?e.args:null,this.callfn("onEnteringState_"+t,e),this.pendingUpdate&&(this.onUpdateActionButtons(t,e),this.pendingUpdate=!1)},t.prototype.onLeavingState=function(t){this.currentPlayerWasActive=!1},t.prototype.onUpdateActionButtons=function(t,e){console.log("(BASICS) onUpdateActionButtons()"),this.curstate!==t?this.pendingUpdate=!0:(this.pendingUpdate=!1,gameui.isCurrentPlayerActive()&&!1===this.currentPlayerWasActive?(console.log("onUpdateActionButtons: "+t,e,this.debugStateInfo()),this.currentPlayerWasActive=!0,this.callfn("onUpdateActionButtons_"+t,e)):this.currentPlayerWasActive=!1)},t.prototype.debugStateInfo=function(){var t=gameui.isCurrentPlayerActive(),e=!1;return"undefined"!=typeof g_replayFrom&&(e=!0),{instantaneousMode:!!gameui.instantaneousMode,isCurrentPlayerActive:t,replayMode:e}},t.prototype.ajaxCallWrapper=function(t,e,o,i){void 0===o&&(o=!1),(e=e||{}).lock=!0,(o||gameui.checkAction(t))&&gameui.ajaxcall("/"+gameui.game_name+"/"+gameui.game_name+"/"+t+".html",e,gameui,function(t){},i)},t.prototype.onScriptError=function(t,e,o){if(!gameui.page_is_unloading)return console.error(t),this.inherited(arguments)},t.prototype.wipeOutAndDestroy=function(t,e){void 0===(e=void 0===e?{}:e).duration&&(e.duration=500),this.instantaneousMode&&(e.duration=Math.min(1,e.duration)),e.node=t;t=dojo.fx.wipeOut(e);dojo.connect(t,"onEnd",function(t){dojo.destroy(t)}),t.play()},t.prototype.placeAndWipeIn=function(t,e,o){void 0===o&&(o={});t=dojo.place(t,e);dojo.setStyle(t,"display","none"),void 0===o.duration&&(o.duration=500),this.instantaneousMode&&(o.duration=Math.min(1,o.duration)),o.node=t,dojo.fx.wipeIn(o).play()},t.prototype.callfn=function(t,e){if(void 0!==this[t])return console.log("Calling "+t,e),this[t](e)},t.prototype.triggerUpdateActionButtons=function(){this.updatePageTitle()},t}(GameGui=function(){}),__extends=this&&this.__extends||function(){var i=function(t,e){return(i=Object.setPrototypeOf||({__proto__:[]}instanceof Array?function(t,e){t.__proto__=e}:function(t,e){for(var o in e)Object.prototype.hasOwnProperty.call(e,o)&&(t[o]=e[o])}))(t,e)};return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Class extends value "+String(e)+" is not a constructor or null");function o(){this.constructor=t}i(t,e),t.prototype=null===e?Object.create(e):(o.prototype=e.prototype,new o)}}(),GameBody=function(o){function t(){var t=o.call(this)||this;return t.mutableBoardState=void 0,t.tablewidePanelEl=void 0,t.inputArgs=null,t.selectedLocation=null,t.selectedCard=null,console.log("effortlesswc constructor"),t}return __extends(t,o),t.prototype.setup=function(t){for(var e in console.log("Starting game setup"),t.players)t.players.hasOwnProperty(e)&&(e=t.players[e],console.log(e));this.setupSeatBoards(t.mutableBoardState),this.setupPlayArea(t.mutableBoardState),this.setupNotifications(),console.log("Ending game setup")},t.prototype.updatePlayerOrdering=function(){console.log("*** updatePlayerOrdering()");for(var t=1,e=0,o=Object.keys(this.gamedatas.playerorder);e<o.length;e++){var i=o[e],i=this.gamedatas.playerorder[i];dojo.place("overall_player_board_"+i,"player_boards",t),t++}},t.prototype.setupSeatBoards=function(t){console.log("setupSeatBoards(): #player_boards =",$("player_boards").children.length),this.tablewidePanelEl=dojo.place(this.format_block("jstpl_tablewide_panel",{}),$("player_boards"),"first"),console.log("setupSeatBoards(): after tablewide panel creation, player_boards =",$("player_boards").children);for(var e=0,o=Object.values(t.seats);e<o.length;e++){var i=o[e];null===i.playerId&&dojo.place(this.format_block("jstpl_seat_board",{seatColor:i.seatColor,seatId:i.id,seatLabel:"Bot A"}),$("player_boards"))}},t.prototype.setupPlayArea=function(t){for(var o=this,i={},e=0,s=Object.values(t.locations);e<s.length;e++){var a=s[e];i[a.sublocationIndex]=a}for(var f=this,r=0;r<6;++r)!function(e){var t=dojo.place(f.format_block("jstpl_setloc_panel",{classes:"ewc_setloc_location_"+i[e].id,id:"ewc_setloc_panel_"+e}),$("ewc_setlocarea_column_"+e%2));dojo.connect(t.querySelector(".ewc_setloc_setloc_wrap"),"onclick",f,function(t){o.onClickLocation(t,i[e].id)})}(r);for(var c=0,n=Object.values(t.effortPiles);c<n.length;c++){var h,l,d=n[c];null!==d.locationId&&(h=t.seats[d.seatId],l=t.locations[d.locationId],l=document.querySelector("#ewc_setloc_panel_"+l.sublocationIndex+" .ewc_effort_counter_wrap"),dojo.place(this.format_block("jstpl_effort_counter",{colorName:h.colorName,id:d.id}),l))}this.applyMutableBoardState(t)},t.prototype.applyMutableBoardState=function(t){var e=this;this.mutableBoardState=t;for(var o=0,i=Object.values(t.locations);o<i.length;o++){var s=i[o];console.log("*** location",s),document.querySelector("#ewc_setloc_panel_"+s.sublocationIndex+" .ewc_setloc_location").classList.add(s.cardType.replace(":","_"))}for(var a=0,f=Object.values(t.effortPiles);a<f.length;a++){var r=f[a];null!==r.locationId&&(document.querySelector("#ewc_effort_counter_"+r.id+" .ewc_effort_counter_value").innerText=""+r.qty)}for(var c=0,n=Object.values(t.settings);c<n.length;c++){var h=n[c];document.querySelector("#ewc_setloc_panel_"+h.sublocationIndex+" .ewc_setloc_setting").classList.add(h.cardType.replace(":","_"))}for(var l=0,d=Object.values(t.cards);l<d.length;l++){var _,p,g=d[l];console.log("*** card",g),"SETLOC"===g.sublocation&&(_=g.visible?g.cardType:"back",p=document.querySelector("#ewc_setloc_panel_"+g.sublocationIndex+" .ewc_setloc_cards"),console.log("*** parentEl",p),dojo.place(this.format_block("jstpl_playarea_card",{cardType:_,id:g.id}),p))}console.log("*** qsa ***"),document.querySelectorAll(".tmp_scalable_card").forEach(function(t){t.classList.contains("tmp_scaled_card")||(t.classList.add("tmp_scaled_card"),e.rescaleSprite(t,.5))}),document.querySelectorAll(".tmp_scalable_cube").forEach(function(t){t.classList.contains("tmp_scaled_cube")||(t.classList.add("tmp_scale_cube"),e.rescaleSpriteCube(t,.6))}),document.querySelectorAll(".tmp_tintable").forEach(function(t){t.classList.contains("tmp_tinted")||(t.classList.add("tmp_tinted"),t.classList.contains("ewc_playercolor_teal")&&e.tintSprite(t,"#00b796"),t.classList.contains("ewc_playercolor_pink")&&e.tintSprite(t,"#ff5fa2"),t.classList.contains("ewc_playercolor_blue")&&e.tintSprite(t,"#001489"),t.classList.contains("ewc_playercolor_yellow")&&e.tintSprite(t,"#ffe900"),t.classList.contains("ewc_playercolor_white")&&e.tintSprite(t,"#ffffff"))})},t.prototype.getSpriteName=function(t){for(var e=0,o=Object.values(t.classList);e<o.length;e++){var i=o[e];if(console.log(i),i.match(/^card_/g))return i}throw new Error("XXX: Unable to find sprite name.")},t.prototype.rescaleSprite=function(t,e){var o=this.getSpriteName(t),o=StaticDataSprites.spriteMetadata[o],i=(t.style.height=o.height*e+"px",t.style.width=o.width*e+"px",StaticDataSprites.totalWidth*e+"px "+StaticDataSprites.totalHeight*e+"px");t.style.backgroundSize=i,t.style.backgroundPosition=o.offsetX*e+"px "+o.offsetY*e+"px"},t.prototype.rescaleSpriteCube=function(t,e){t.style.height=30*e+"px",t.style.width=30*e+"px";var o=312*e+"px "+302.4*e+"px",o=(t.style.backgroundSize=o,t.style.maskSize=o,-276*e+"px "+-121.2*e+"px");t.style.backgroundPosition=o,t.style.maskPosition=o},t.prototype.tintSprite=function(t,e){t.style.backgroundBlendMode="multiply",t.style.backgroundColor=e},t.prototype.updateSelectables=function(t){var i=this;switch(console.log("*** updateSelectables()"),document.querySelectorAll(".ewc_selectable").forEach(function(t){t.classList.remove("ewc_selectable")}),document.querySelectorAll(".ewc_unselectable").forEach(function(t){t.classList.remove("ewc_unselectable")}),document.querySelectorAll(".ewc_selected").forEach(function(t){t.classList.remove("ewc_selected")}),t.inputType){case"inputtype:location":console.log("  *** inputtype:location");for(var e=0,o=t.choices;e<o.length;e++){var s=o[e];document.querySelector(".ewc_setloc_location_"+s+" .ewc_setloc_setloc_wrap").classList.add("ewc_selectable")}document.querySelectorAll(".ewc_setloc_setloc_wrap:not(.ewc_selectable)").forEach(function(t){t.classList.add("ewc_unselectable")});break;case"inputtype:card":console.log("  *** inputtype:card",t),this.placeAndWipeIn(this.format_block("jstpl_promptarea",{}),"ewc_promptarea_wrap");for(var a=this,f=0,r=Object.values(t.choices);f<r.length;f++)!function(t){var e=t,t=e.visible?e.cardType:"back",o=document.querySelector(".ewc_promptarea .ewc_promptarea_choices"),t=dojo.place(a.format_block("jstpl_prompt_card",{cardType:t,id:e.id}),o);a.rescaleSprite(t,.35),t.classList.add("ewc_selectable"),dojo.connect(t,"onclick",a,function(t){i.onClickCard(t,e.id)})}(r[f]);break;case"inputtype:effort-pile":console.log("  *** inputtype:effort-pile");for(var c=0,n=t.choices;c<n.length;c++){s=n[c];document.querySelector(".ewc_effort_counter_"+s+" .ewc_setloc_setloc_wrap").classList.add("ewc_selectable")}document.querySelectorAll(".ewc_effort_counter:not(.ewc_selectable)").forEach(function(t){t.classList.add("ewc_unselectable")});break;default:throw new Error("Unexpected input type: "+t.inputType)}},t.prototype.onClickLocation=function(t,e){console.log("onClickLocation",t),document.querySelectorAll(".ewc_selected").forEach(function(t){t.classList.remove("ewc_selected")}),t.currentTarget.classList.add("ewc_selected"),this.selectedLocation=e,this.triggerUpdateActionButtons()},t.prototype.onClickCard=function(t,e){console.log("onClickCard",t),document.querySelectorAll(".ewc_selected").forEach(function(t){t.classList.remove("ewc_selected")}),t.currentTarget.classList.add("ewc_selected"),this.selectedCard=e,this.triggerUpdateActionButtons()},t.prototype.onEnteringState=function(t,e){console.log("Entering state",t,e),o.prototype.onEnteringState.call(this,t,e),"stInput"===t&&this.isCurrentPlayerActive()&&(console.log("*** stInput: ",e),this.inputArgs=e.args.input,this.updateSelectables(e.args.input))},t.prototype.onLeavingState=function(t){var e=this;console.log("Leaving state: "+t),o.prototype.onLeavingState.call(this,t),this.inputArgs=null,document.querySelectorAll(".ewc_promptarea").forEach(function(t){e.wipeOutAndDestroy(t)})},t.prototype.onUpdateActionButtons=function(t,e){var o=this;if(console.log("onUpdateActionButtons()",t,e),this.isCurrentPlayerActive()&&"stInput"===t){this.addActionButton("btn_input_confirm",_("Confirm"),function(){var t=null;switch(o.inputArgs.inputType){case"inputtype:location":t={selection:JSON.stringify({inputType:"inputtype:location",value:o.selectedLocation})};break;case"inputtype:card":t={selection:JSON.stringify({inputType:"inputtype:card",value:o.selectedCard})};break;default:throw new Error("Unexpected input type.")}console.log("confirmed!",t),o.ajaxCallWrapper("actSelectInput",t)},void 0,void 0,"blue");var i=!1;if(null!==this.inputArgs)switch(this.inputArgs.inputType){case"inputtype:location":i=null!==this.selectedLocation;break;case"inputtype:card":i=null!==this.selectedCard;break;default:throw new Error("Unexpected input type.")}i||dojo.addClass("btn_input_confirm","disabled")}},t.prototype.setupNotifications=function(){console.log("notifications subscriptions setup")},t}(GameBasics),StaticDataSprites=(define(["dojo","dojo/_base/declare","ebg/core/gamegui","ebg/counter","ebg/stock","ebg/zone"],function(t,e){e("bgagame.effortlesswc",ebg.core.gamegui,new GameBody)}),function(){function t(){}return t.totalWidth=3517.2,t.totalHeight=3328.2,t.spriteMetadata={card_armor_assassin_chest:{width:233.4,height:363.6,offsetX:0,offsetY:0},card_armor_assassin_feet:{width:233.4,height:363.6,offsetX:-233.4,offsetY:0},card_armor_assassin_hands:{width:233.4,height:363.6,offsetX:-466.8,offsetY:0},card_armor_assassin_head:{width:233.4,height:363.6,offsetX:-700.2,offsetY:0},card_armor_leather_chest:{width:233.4,height:363.6,offsetX:0,offsetY:-363.6},card_armor_leather_feet:{width:233.4,height:363.6,offsetX:-233.4,offsetY:-363.6},card_armor_leather_hands:{width:233.4,height:363.6,offsetX:-466.8,offsetY:-363.6},card_armor_leather_head:{width:233.4,height:363.6,offsetX:-700.2,offsetY:-363.6},card_armor_mage_chest:{width:233.4,height:363.6,offsetX:-933.6,offsetY:0},card_armor_mage_feet:{width:233.4,height:363.6,offsetX:-933.6,offsetY:-363.6},card_armor_mage_hands:{width:233.4,height:363.6,offsetX:0,offsetY:-727.2},card_armor_mage_head:{width:233.4,height:363.6,offsetX:-233.4,offsetY:-727.2},card_armor_obsidian_chest:{width:233.4,height:363.6,offsetX:-466.8,offsetY:-727.2},card_armor_obsidian_feet:{width:233.4,height:363.6,offsetX:-700.2,offsetY:-727.2},card_armor_obsidian_hands:{width:233.4,height:363.6,offsetX:-933.6,offsetY:-727.2},card_armor_obsidian_head:{width:233.4,height:363.6,offsetX:-1167,offsetY:0},card_armor_plate_chest:{width:233.4,height:363.6,offsetX:-1167,offsetY:-363.6},card_armor_plate_feet:{width:233.4,height:363.6,offsetX:-1167,offsetY:-727.2},card_armor_plate_hands:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:0},card_armor_plate_head:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:-363.6},card_armor_scale_chest:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:-727.2},card_armor_scale_feet:{width:233.4,height:363.6,offsetX:0,offsetY:-1090.8},card_armor_scale_hands:{width:233.4,height:363.6,offsetX:-233.4,offsetY:-1090.8},card_armor_scale_head:{width:233.4,height:363.6,offsetX:-466.8,offsetY:-1090.8},card_attr_cha_1:{width:233.4,height:363.6,offsetX:-700.2,offsetY:-1090.8},card_attr_cha_2:{width:233.4,height:363.6,offsetX:-933.6,offsetY:-1090.8},card_attr_con_1:{width:233.4,height:363.6,offsetX:-1167,offsetY:-1090.8},card_attr_con_2:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:-1090.8},card_attr_dex_1:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:0},card_attr_dex_2:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:-363.6},card_attr_int_1:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:-727.2},card_attr_int_2:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:-1090.8},card_attr_str_1:{width:233.4,height:363.6,offsetX:0,offsetY:-1454.4},card_attr_str_2:{width:233.4,height:363.6,offsetX:-233.4,offsetY:-1454.4},card_attr_wis_1:{width:233.4,height:363.6,offsetX:-466.8,offsetY:-1454.4},card_attr_wis_2:{width:233.4,height:363.6,offsetX:-700.2,offsetY:-1454.4},card_back:{width:233.4,height:363.6,offsetX:-933.6,offsetY:-1454.4},card_dwarf:{width:233.4,height:363,offsetX:-2567.4,offsetY:-2181.6},card_elf:{width:233.4,height:363,offsetX:-2800.8,offsetY:0},card_exp:{width:233.4,height:363.6,offsetX:-1167,offsetY:-1454.4},card_fairy:{width:233.4,height:363,offsetX:-2800.8,offsetY:-363},card_gnome:{width:233.4,height:363,offsetX:-2800.8,offsetY:-726},card_goblin:{width:233.4,height:363,offsetX:-2800.8,offsetY:-1089},card_grit:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:-1454.4},card_human:{width:233.4,height:363,offsetX:-2800.8,offsetY:-1452},card_item_1:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:-1454.4},card_item_10:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:0},card_item_11:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:-363.6},card_item_12:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:-727.2},card_item_13:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:-1090.8},card_item_14:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:-1454.4},card_item_15:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:0},card_item_16:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:-363.6},card_item_17:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:-727.2},card_item_18:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:-1090.8},card_item_19:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:-1454.4},card_item_2:{width:233.4,height:363.6,offsetX:0,offsetY:-1818},card_item_20:{width:233.4,height:363.6,offsetX:-233.4,offsetY:-1818},card_item_21:{width:233.4,height:363.6,offsetX:-466.8,offsetY:-1818},card_item_3:{width:233.4,height:363.6,offsetX:-700.2,offsetY:-1818},card_item_4:{width:233.4,height:363.6,offsetX:-933.6,offsetY:-1818},card_item_5:{width:233.4,height:363.6,offsetX:-1167,offsetY:-1818},card_item_6:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:-1818},card_item_7:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:-1818},card_item_8:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:-1818},card_item_9:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:-1818},card_orc:{width:233.4,height:363,offsetX:-2800.8,offsetY:-1815},class_alchemist:{width:233.4,height:363,offsetX:-2800.8,offsetY:-2178},class_artificer:{width:233.4,height:363,offsetX:0,offsetY:-2545.2},class_barbarian:{width:233.4,height:363,offsetX:-233.4,offsetY:-2545.2},class_bard:{width:233.4,height:363,offsetX:-466.8,offsetY:-2545.2},class_cleric:{width:233.4,height:363,offsetX:-700.2,offsetY:-2545.2},class_druid:{width:233.4,height:363,offsetX:-933.6,offsetY:-2545.2},class_fighter:{width:233.4,height:363,offsetX:-1167,offsetY:-2545.2},class_merchant:{width:233.4,height:363,offsetX:-1400.4,offsetY:-2545.2},class_monk:{width:233.4,height:363,offsetX:-1633.8,offsetY:-2545.2},class_necromancer:{width:233.4,height:363,offsetX:-1867.2,offsetY:-2545.2},class_paladin:{width:233.4,height:363,offsetX:-2100.6,offsetY:-2545.2},class_ranger:{width:233.4,height:363,offsetX:-2334,offsetY:-2545.2},class_rogue:{width:233.4,height:363,offsetX:-2567.4,offsetY:-2545.2},class_wizard:{width:233.4,height:363,offsetX:-2800.8,offsetY:-2545.2},threat_threat_1:{width:233.4,height:363.6,offsetX:-2334,offsetY:0},threat_threat_10:{width:233.4,height:363.6,offsetX:-2334,offsetY:-363.6},threat_threat_11:{width:233.4,height:363.6,offsetX:-2334,offsetY:-727.2},threat_threat_12:{width:233.4,height:363.6,offsetX:-2334,offsetY:-1090.8},threat_threat_13:{width:233.4,height:363.6,offsetX:-2334,offsetY:-1454.4},threat_threat_14:{width:233.4,height:363.6,offsetX:-2334,offsetY:-1818},threat_threat_15:{width:233.4,height:363.6,offsetX:0,offsetY:-2181.6},threat_threat_16:{width:233.4,height:363.6,offsetX:-233.4,offsetY:-2181.6},threat_threat_17:{width:233.4,height:363.6,offsetX:-466.8,offsetY:-2181.6},threat_threat_18:{width:233.4,height:363.6,offsetX:-700.2,offsetY:-2181.6},threat_threat_19:{width:233.4,height:363.6,offsetX:-933.6,offsetY:-2181.6},threat_threat_2:{width:233.4,height:363.6,offsetX:-1167,offsetY:-2181.6},threat_threat_20:{width:233.4,height:363.6,offsetX:-1400.4,offsetY:-2181.6},threat_threat_21:{width:233.4,height:363.6,offsetX:-1633.8,offsetY:-2181.6},threat_threat_3:{width:233.4,height:363.6,offsetX:-1867.2,offsetY:-2181.6},threat_threat_4:{width:233.4,height:363.6,offsetX:-2100.6,offsetY:-2181.6},threat_threat_5:{width:233.4,height:363.6,offsetX:-2334,offsetY:-2181.6},threat_threat_6:{width:233.4,height:363.6,offsetX:-2567.4,offsetY:0},threat_threat_7:{width:233.4,height:363.6,offsetX:-2567.4,offsetY:-363.6},threat_threat_8:{width:233.4,height:363.6,offsetX:-2567.4,offsetY:-727.2},threat_threat_9:{width:233.4,height:363.6,offsetX:-2567.4,offsetY:-1090.8},threat_threat_back:{width:233.4,height:363.6,offsetX:-2567.4,offsetY:-1454.4},threat_threat_vacant:{width:233.4,height:363.6,offsetX:-2567.4,offsetY:-1818},location_cabin:{width:300,height:210,offsetX:-3034.2,offsetY:0},location_caravan:{width:300,height:210,offsetX:-3034.2,offsetY:-210},location_cave:{width:300,height:210,offsetX:-3034.2,offsetY:-420},location_city:{width:300,height:210,offsetX:-3034.2,offsetY:-630},location_coliseum:{width:300,height:210,offsetX:-3034.2,offsetY:-840},location_crypt:{width:300,height:210,offsetX:-3034.2,offsetY:-1050},location_docks:{width:300,height:210,offsetX:-3034.2,offsetY:-1260},location_dungeon:{width:300,height:210,offsetX:-3034.2,offsetY:-1470},location_forest:{width:300,height:210,offsetX:-3034.2,offsetY:-1680},location_garden:{width:300,height:210,offsetX:-3034.2,offsetY:-1890},location_laboratory:{width:300,height:210,offsetX:-3034.2,offsetY:-2100},location_labyrinth:{width:300,height:210,offsetX:-3034.2,offsetY:-2310},location_library:{width:300,height:210,offsetX:-3034.2,offsetY:-2520},location_market:{width:300,height:210,offsetX:0,offsetY:-2908.2},location_observatory:{width:300,height:210,offsetX:-300,offsetY:-2908.2},location_portal:{width:300,height:210,offsetX:-600,offsetY:-2908.2},location_prison:{width:300,height:210,offsetX:-900,offsetY:-2908.2},location_river:{width:300,height:210,offsetX:-1200,offsetY:-2908.2},location_stables:{width:300,height:210,offsetX:-1500,offsetY:-2908.2},location_temple:{width:300,height:210,offsetX:-1800,offsetY:-2908.2},location_tunnels:{width:300,height:210,offsetX:-2100,offsetY:-2908.2},location_wasteland:{width:300,height:210,offsetX:-2400,offsetY:-2908.2},setting_active:{width:183,height:210,offsetX:-2700,offsetY:-2908.2},setting_barren:{width:183,height:210,offsetX:-2883,offsetY:-2908.2},setting_battling:{width:183,height:210,offsetX:-3066,offsetY:-2908.2},setting_capable:{width:183,height:210,offsetX:0,offsetY:-3118.2},setting_corrupted:{width:183,height:210,offsetX:-183,offsetY:-3118.2},setting_crowded:{width:183,height:210,offsetX:-366,offsetY:-3118.2},setting_eerie:{width:183,height:210,offsetX:-549,offsetY:-3118.2},setting_equipped:{width:183,height:210,offsetX:-732,offsetY:-3118.2},setting_ghostly:{width:183,height:210,offsetX:-915,offsetY:-3118.2},setting_hidden:{width:183,height:210,offsetX:-1098,offsetY:-3118.2},setting_holy:{width:183,height:210,offsetX:-1281,offsetY:-3118.2},setting_lively:{width:183,height:210,offsetX:-1464,offsetY:-3118.2},setting_magical:{width:183,height:210,offsetX:-1647,offsetY:-3118.2},setting_nonexistent:{width:183,height:210,offsetX:-1830,offsetY:-3118.2},setting_overgrown:{width:183,height:210,offsetX:-2013,offsetY:-3118.2},setting_peaceful:{width:183,height:210,offsetX:-2196,offsetY:-3118.2},setting_quiet:{width:183,height:210,offsetX:-2379,offsetY:-3118.2},setting_secret:{width:183,height:210,offsetX:-2562,offsetY:-3118.2},setting_sheltered:{width:183,height:210,offsetX:-2745,offsetY:-3118.2},setting_starved:{width:183,height:210,offsetX:-2928,offsetY:-3118.2},setting_transcendent:{width:183,height:210,offsetX:-3111,offsetY:-3118.2},setting_traveling:{width:183,height:210,offsetX:-3334.2,offsetY:0},setting_treacherous:{width:183,height:210,offsetX:-3334.2,offsetY:-210}},t}());