<?php declare(strict_types=1);

define('LOCATION_METADATA', [
    'coliseum' => [
        'name' => clienttranslate('Coliseum'),
        'text' => clienttranslate(
            'Take one card from here and discard the other.'
        ),
    ],
    'library' => [
        'name' => clienttranslate('Library'),
        'text' => clienttranslate(
            'View both cards here and take 1.  Replace the missing card face-down.'
        ),
    ],
    'market' => [
        'name' => clienttranslate('Market'),
        'text' => clienttranslate(
            'Discard a card from your hand and take both cards here.'
        ),
    ],
    'cave' => [
        'name' => clienttranslate('Cave'),
        'text' => clienttranslate(''),
    ],
    'river' => [
        'name' => clienttranslate('River'),
        'text' => clienttranslate('Discard a card at another location.'),
    ],
    'prison' => [
        'name' => clienttranslate('Prison'),
        'text' => clienttranslate(
            'Move another player’s effort from any other location to here.'
        ),
    ],
    'tunnels' => [
        'name' => clienttranslate('Tunnels'),
        'text' => clienttranslate(
            'Move another player’s effort from here to any other location.'
        ),
    ],
    'city' => [
        'name' => clienttranslate('City'),
        'text' => clienttranslate(
            'Move one of your effort from any other location to here.'
        ),
    ],
    'wasteland' => [
        'name' => clienttranslate('Wasteland'),
        'text' => clienttranslate(''),
    ],
    'docks' => [
        'name' => clienttranslate('Docks'),
        'text' => clienttranslate(
            'Move one of your effort from here to any other location.'
        ),
    ],
    'temple' => [
        'name' => clienttranslate('Temple'),
        'text' => clienttranslate(
            'Discard a card from your hand to take the top 2 cards from the deck.'
        ),
    ],
    'crypt' => [
        'name' => clienttranslate('Crypt'),
        'text' => clienttranslate(
            'Take 1 of the top 2 cards from the discard.'
        ),
    ],
    'tundra' => [
        'name' => clienttranslate('Tundra'),
        'text' => clienttranslate(
            'Once all players have placed half of their effort, replace this location at random.'
        ),
    ],
]);
define('SETTING_METADATA', [
    'active' => [
        'name' => clienttranslate('Active'),
        'text' => clienttranslate('Most here gains 4 points.'),
    ],
    'crowded' => [
        'name' => clienttranslate('Crowded'),
        'text' => clienttranslate(
            'Gain 10 points if you have at least 5 effort here.'
        ),
    ],
    'lively' => [
        'name' => clienttranslate('Lively'),
        'text' => clienttranslate('Gain 1 point for each effort here.'),
    ],
    'peaceful' => [
        'name' => clienttranslate('Peaceful'),
        'text' => clienttranslate('Gain 3 points for every 2 effort here.'),
    ],
    'battling' => [
        'name' => clienttranslate('Battling'),
        'text' => clienttranslate('Most here gains 8 points.'),
    ],
    'barren' => [
        'name' => clienttranslate('Barren'),
        'text' => clienttranslate(''),
    ],
    'hidden' => [
        'name' => clienttranslate('Hidden'),
        'text' => clienttranslate('Least here loses 5 points.'),
    ],
    'treacherous' => [
        'name' => clienttranslate('Treacherous'),
        'text' => clienttranslate('Lose 1 point for each effort here.'),
    ],
    'quiet' => [
        'name' => clienttranslate('Quiet'),
        'text' => clienttranslate('Most here loses 5 points.'),
    ],
    'eerie' => [
        'name' => clienttranslate('Eerie'),
        'text' => clienttranslate('Least here gains 5 points.'),
    ],
    'holy' => [
        'name' => clienttranslate('Holy'),
        'text' => clienttranslate('Most here gains 2 points for each effort.'),
    ],
    'ghostly' => [
        'name' => clienttranslate('Ghostly'),
        'text' => clienttranslate('Lose 2 points for every 2 effort here.'),
    ],
    'frozen' => [
        'name' => clienttranslate('Frozen'),
        'text' => clienttranslate(
            'Once all players have placed half of their effort, replace this setting at random.'
        ),
    ],
]);
