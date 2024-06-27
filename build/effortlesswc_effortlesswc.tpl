{OVERALL_GAME_HEADER}

<div id="ewc_playarea" class="ewc_playarea">

  <div id="ewc_handarea" class="ewc_handarea">
  </div>

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

let jstpl_setloc_panel =
  '<div id="${id}" class="ewc_setloc_panel ${classes}">' +
  '<div class="ewc_setloc_setloc_wrap">' +
  '<div class="ewc_setloc_setting_wrap">' +

  '<div class="ewc_setloc_setting">' +
  /* '<span style="position:absolute; bottom:0px; right:10px; margin-right:30px;">XXX</span>' +
   */
  '<div class="ewc_effort_counter_wrap">' +
  /* '<div class="ewc_effort_counter">X &#x2715; 4</div>' +
   * '<div class="ewc_effort_counter">X &#x2715; 5</div>' + */
  '<div class="ewc_effort_counter"><div class="icon_effort tmp_tintable tmp_offset_cube tmp_scalable_cube ewc_playercolor_teal"></div> 4</div>' +
  '<div class="ewc_effort_counter"><div class="icon_effort tmp_tintable tmp_offset_cube tmp_scalable_cube ewc_playercolor_pink"></div> 5</div>' +
  '</div>' +

  /* '<div class="icon_effort tmp_tintable tmp_offset_cube"></div>' + */
        //  <div class="ewc_setloc_effort"></div>' +
  /* '</div>' +  // .ewc_setloc_player_effort */
  '</div>' +  // .ewc_setloc_setting

  '</div>' + // .ewc_setloc_setting_wrap
      '<div class="ewc_setloc_location_wrap">' +
        '<div class="ewc_setloc_location">' +
        '</div>' +  // .ewc_setloc_location
  '</div>' +    // .ewc_setloc_location_wrap
  '</div>' +    // .ewc_setloc_setloc_wrap
  '<div class="ewc_setloc_cards_wrap">' +
    '<div class="ewc_setloc_cards">' +
  /* '<div class="ewc_card_wrap">' + */
  /* '<div id="XXX_card_123" class="ewc_card card_attr_cha_1 ewc_card_playarea tmp_scalable"></div>' +
   * '<div id="XXX_card_124" class="ewc_card card_attr_cha_1 ewc_card_playarea tmp_scalable"></div>' +
   * '<div id="XXX_card_125" class="ewc_card card_attr_cha_1 ewc_card_playarea tmp_scalable"></div>' + */
  /* '</div>' + */
    '</div>' +
  '</div>' +  // .ewc_setloc_cards_wrap
  //'<div class="ewc_setloc_threat"></div>' +
  '</div>';  // .ewc_setloc_panel

let jstpl_playarea_card =
  '<div id="cardid_${id}" class="ewc_card card_${cardType} ewc_card_playarea tmp_scalable_card"></div>';

</script>

{OVERALL_GAME_FOOTER}
