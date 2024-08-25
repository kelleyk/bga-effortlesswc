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
        'text' => clienttranslate('(No effect.)'),
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
        'text' => clienttranslate('(No effect.)'),
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
        'text' => clienttranslate(
            'The player with the most effort here gains 4 points.'
        ),
    ],
    'crowded' => [
        'name' => clienttranslate('Crowded'),
        'text' => clienttranslate(
            'Each player with at least 5 effort here gains 10 points.'
        ),
    ],
    'lively' => [
        'name' => clienttranslate('Lively'),
        'text' => clienttranslate(
            'Each player gains 1 point for each effort they have here.'
        ),
    ],
    'peaceful' => [
        'name' => clienttranslate('Peaceful'),
        'text' => clienttranslate(
            'Each player gains 3 points for every 2 effort they have here.'
        ),
    ],
    'battling' => [
        'name' => clienttranslate('Battling'),
        'text' => clienttranslate(
            'The player with the most effort here gains 8 points.'
        ),
    ],
    'barren' => [
        'name' => clienttranslate('Barren'),
        'text' => clienttranslate('(No effect.)'),
    ],
    'hidden' => [
        'name' => clienttranslate('Hidden'),
        'text' => clienttranslate(
            'The player with the least effort here loses 5 points.'
        ),
    ],
    'treacherous' => [
        'name' => clienttranslate('Treacherous'),
        'text' => clienttranslate(
            'Each player loses 1 point for each effort they have here.'
        ),
    ],
    'quiet' => [
        'name' => clienttranslate('Quiet'),
        'text' => clienttranslate(
            'The player with the most effort here loses 5 points.'
        ),
    ],
    'eerie' => [
        'name' => clienttranslate('Eerie'),
        'text' => clienttranslate(
            'The player with the least effort here gains 5 points.'
        ),
    ],
    'holy' => [
        'name' => clienttranslate('Holy'),
        'text' => clienttranslate(
            'The player with the most effort here gains 2 points for each effort.'
        ),
    ],
    'ghostly' => [
        'name' => clienttranslate('Ghostly'),
        'text' => clienttranslate(
            'Each player loses 2 points for every 2 effort here.'
        ),
    ],
    'frozen' => [
        'name' => clienttranslate('Frozen'),
        'text' => clienttranslate(
            'Once all players have placed half of their effort, this setting will be replaced at random.'
        ),
    ],
]);
