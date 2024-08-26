{OVERALL_GAME_HEADER}

<script>
  if (window.bgagame === undefined) {
    window.bgagame = {};
  }
</script>

<div id="ewc_playarea" class="ewc_playarea">

  <div id="ewc_handarea" class="ewc_handarea">
    <div id="ewc_handarea_zone"></div>
  </div>

  <!-- This wrapper is used so that we know where to create prompts. -->
  <div id="ewc_promptarea_wrap"></div>

  <!-- This wrapper is used so that we know where to show scoring details at the end of the game. -->
  <div id="ewc_scoringarea_wrap"></div>

  <div id="ewc_setlocarea" class="ewc_setlocarea">
    <div id="ewc_setlocarea_column_0" class="ewc_setlocarea_column">
    </div>
    <div id="ewc_setlocarea_column_1" class="ewc_setlocarea_column">
    </div>
  </div>

</div>

<svg width="0" height="0">
  <defs>
    <clipPath id="ewc_clip_puzzle">
      <!-- TODO: We should be able to get rid of the translate() here by baking it in to the path. -->
      <!-- TODO: I wonder if something like https://jamesmcgrath.net/scaling-svg-clippath/ will help us when we start wanting to be able to responsively scale the setloc pieces. -->
      <!--  scale(1.3,1.3) -->
      <!-- translate(-23.796 -22.11) -->
      <path transform="scale(1.37, 1.37) translate(-23.796 -22.11)" d="m23.796 22.11v153.54h107.48v-35.072l18.325 5.5035a3.3249 2.7332 0 0 0 4.4659-2.5673v-35.684a3.3249 2.7332 0 0 0-4.4659-2.5668l-18.325 5.5035v-88.653z" />
    </clipPath>
  </defs>
</svg>

<script type="text/javascript">
/** XXX: Find a better way to define templates. **/

  // N.B.: The .ewc_setloc_setloc_wrap element needs a unique ID for `addTooltipHtml()`.
let jstpl_setloc_panel =
  '<div id="${id}" class="ewc_setloc_panel ${classes}">' +
  '<div id="${id}_wrap" class="ewc_setloc_setloc_wrap">' +
  '<div class="ewc_setloc_setting_wrap">' +

  '<div class="ewc_setloc_setting">' +
  '<div class="ewc_effort_counter_wrap"></div>' +
  '</div>' +  // .ewc_setloc_setting

  '</div>' + // .ewc_setloc_setting_wrap
      '<div class="ewc_setloc_location_wrap">' +
        '<div class="ewc_setloc_location">' +
        '</div>' +  // .ewc_setloc_location
  '</div>' +    // .ewc_setloc_location_wrap
  '</div>' +    // .ewc_setloc_setloc_wrap
  '<div class="ewc_setloc_cards_wrap">' +
    '<div class="ewc_setloc_cards">' +
    '</div>' +
  '</div>' +  // .ewc_setloc_cards_wrap
  //'<div class="ewc_setloc_threat"></div>' +
  '</div>';  // .ewc_setloc_panel

let jstpl_playarea_card =
  '<div id="cardid_${id}" class="ewc_card card_${cardType} ewc_card_playarea tmp_scalable_card"></div>';

let jstpl_hand_card =
  '<div id="cardid_${id}" class="ewc_card card_${cardType} ewc_card_hand tmp_scalable_card"></div>';

  // XXX: Does this create a problem?  A card may be shown in a prompt and *also* in either the hand or the play-area, which means that these div IDs are not unique.
  //
  // XXX: The prefix here was changed to "#cardidprompt_" in an attempt to address this.
  //
  // XXX: Note that this template does not include ".tmp_card_scalable"; that class causes `rescaleCardSprites()` to rescale prompt cards as though they were a play-area cards.  That class isn't necessary for the actual rescaling mechanism anyway; perhaps we should remove it.
let jstpl_prompt_card =
  '<div id="cardidprompt_${id}" class="ewc_card card_${cardType} ewc_card_prompt"></div>';

let jstpl_effort_counter =
  '<div id="ewc_effort_counter_${id}" class="ewc_effort_counter"><div class="icon_effort tmp_tintable tmp_offset_cube tmp_scalable_cube ewc_playercolor_${colorName}"></div> <span class="ewc_effort_counter_value"></span></div>';

// N.B.: We use ".player-board" just for the appearance.
var jstpl_tablewide_panel =
  '<div class="tablewide_panel player-board" id="tablewide_panel">' +
  '(discard-pile button)' +
  '</div>';

  var jstpl_seat_board_contents =
    '<div id="ewc_effort_counter_${reservePileId}" class="ewc_effort_counter_reserve"><div class="icon_effort tmp_tintable tmp_offset_cube tmp_scalable_cube ewc_playercolor_${colorName}"></div> <span class="ewc_effort_counter_value"></span></div>';

  var jstpl_seat_board =
    '<div id="overall_seat_board_${seatId}" class="player-board current-player-board" style="width: 234px; height: auto;">'+
    '<div class="player_board_inner">'+
    '<div class="emblemwrap is_premium" id="avatarwrap_${seatId}">'+
    '<img src="https://studio.boardgamearena.com:8084/data/avatar/default_32.jpg" alt="" class="avatar emblem" id="avatar_${seatId}">'+
    '<div class="emblempremium" id="db459fe1-ae5c-40b2-8652-7e5f635747be"></div>'+
    '</div>'+

    '<div class="emblemwrap" id="avatar_active_wrap_${seatId}" style="display:none">'+
    '<img src="https://studio.boardgamearena.com:8084/data/themereleases/240626-1003/img/layout/active_player.gif" alt="" class="avatar avatar_active" id="avatar_active_${seatId}">'+
    '<div class="icon20 icon20_night this_is_night"></div>'+
    '</div>'+

    '<div class="player-name" id="player_name_${seatId}">'+
    '	<span style="color: #${seatColor}">${seatLabel}</span>'+
    // '<i id="seat_${seatId}_status" class="fa fa-circle status_online player_status"></i>'+
    // '<div class="bga-flag" data-country="US"></div>'+
    '</div>'+
    '<div id="player_board_${seatId}" class="player_board_content">'+
    '<div class="player_score">'+
    '<span id="seat_score_${seatId}" class="player_score_value">-</span> <i class="fa fa-star" id="icon_point_${seatId}"></i>'+
    // '<span class="player_elo_wrap">• <div class="gamerank gamerank_beginner "><span class="icon20 icon20_rankw "></span> <span class="gamerank_value" id="player_elo_${seatId}">0</span></div></span>'+
    // '<span class="timeToThink">&mdash;</span>'+
    '</div>'+
    '<div class="player_showcursor" id="seat_showcursor_${seatId}"><input type="checkbox" checked="checked" class="player_hidecursor" id="player_hidecursor_${seatId}"> Show cursor <i class="fa fa-hand-pointer-o" style="color:#001489"></i></div>'+
    '<div class="player_table_status" id="seat_table_status_${seatId}" style="display: none;"></div>'+
    '</div>'+
    '<div id="current_player_board">'+

    '</div>'+
    '<div id="seat_panel_conent_${seatId}" class="player_panel_content">'+
    '</div>'+
    '</div>'+
    '</div>';

let jstpl_promptarea =
  '<div id="ewc_promptarea" class="ewc_promptarea">' +
  '<div class="ewc_promptarea_choices"></div>' +
  '</div>';

let jstpl_scoringarea =
  '<div id="ewc_scoringarea" class="ewc_scoringarea">' +
  '<div class="ewc_scoringarea_choices"></div>' +
  '</div>';

  let jstpl_tooltip_card =
    '<div class="tooltip-container ewc_tooltip">${title}</div>';

  let jstpl_tooltip_setloc =
    '<div class="tooltip-container ewc_tooltip">' +
    '<strong>${setting.name}</strong><br />${setting.text}<br />' +
    '<strong>${location.name}</strong><br />${location.text}' +
    '</div>';

</script>

{OVERALL_GAME_FOOTER}
