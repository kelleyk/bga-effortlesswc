<?php declare(strict_types=1);

/** @var (string|int|null|bool|string[]|int[])[] $gameinfos */
$gameinfos = [
  'game_name' => 'Effortless',
  'publisher' => 'Barn Made Games',
  'publisher_website' => 'https://www.barnmadegames.com/',
  'publisher_bgg_id' => 54543,
  'bgg_id' => 396716,
  // TODO: Solo mode is possible, but not yet implemented.
  'players' => [2, 3, 4, 5],
  // N.B.: These are official player colors from the designer.
  'player_colors' => [
    '001489', // blue
    'ff5fa2', // pink
    '00b796', // teal
    'ffe900', // yellow
    'ffffff', // white
  ],
  // XXX: Metadata below this line hasn't been filled in yet.
  'favorite_colors_support' => true,
  'suggest_player_number' => null,
  'not_recommend_player_number' => null,
  'disable_player_order_swap_on_rematch' => false,
  'estimated_duration' => 30,
  'fast_additional_time' => 30,
  'medium_additional_time' => 40,
  'slow_additional_time' => 50,
  'tie_breaker_description' => '',
  'losers_not_ranked' => false,
  'solo_mode_ranked' => false,
  'is_coop' => 0,
  'is_beta' => 1,
  'language_dependency' => false,
  'game_interface_width' => [
    'min' => 740,
    'max' => null,
  ],
  'is_sandbox' => false,
  'turnControl' => 'simple',
];
