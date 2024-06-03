{OVERALL_GAME_HEADER}

<div id="ewc_playarea" class="ewc_playarea">

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
  '<div class="ewc_setloc_setting_wrap">' +
    '<div class="ewc_setloc_setting">' +
    //  <div class="ewc_setloc_effort"></div>' +
    '</div>' +
  '</div>' +
  '<div class="ewc_setloc_location_wrap">' +
    '<div class="ewc_setloc_location">' +
    '</div>' +
  '</div>' +
  '<div class="ewc_setloc_cards_wrap">' +
    '<div class="ewc_setloc_cards">' +
      '<div class="ewc_card_wrap">' +
        '<div class="ewc_card card_attr_cha_1 ewc_card_playarea tmp_scalable"></div>' +
      '</div>' +
    '</div>' +
  '</div>' +
  //'<div class="ewc_setloc_threat"></div>' +
  '</div>';

</script>

{OVERALL_GAME_FOOTER}
