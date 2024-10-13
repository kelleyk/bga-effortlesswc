class StaticDataSetlocs {
  static locationMetadata: any = {
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
    cave: { name: 'Cave', text: '(No effect.)' },
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
    wasteland: { name: 'Wasteland', text: '(No effect.)' },
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
  static settingMetadata: any = {
    active: {
      name: 'Active',
      text: 'Each player with at least 1 effort here gains 3 points.',
    },
    crowded: {
      name: 'Crowded',
      text: 'Each player with at least 5 effort here gains 10 points.',
    },
    lively: {
      name: 'Lively',
      text: 'Each player gains 1 point for each effort they have here.',
    },
    peaceful: {
      name: 'Peaceful',
      text: 'Each player gains 3 points for every 2 effort they have here.',
    },
    battling: {
      name: 'Battling',
      text: 'The player with the most effort here gains 8 points.',
    },
    barren: { name: 'Barren', text: '(No effect.)' },
    hidden: {
      name: 'Hidden',
      text: 'The player with the least effort here loses 5 points.',
    },
    treacherous: {
      name: 'Treacherous',
      text: 'Each player loses 1 point for each effort they have here.',
    },
    quiet: {
      name: 'Quiet',
      text: 'The player with the most effort here loses 3 points.',
    },
    eerie: {
      name: 'Eerie',
      text: 'The player with the least effort here gains 3 points.',
    },
    holy: {
      name: 'Holy',
      text: 'The player with the most effort here gains 1 point for each effort they have here.',
    },
    ghostly: {
      name: 'Ghostly',
      text: 'The player with the most effort here loses 1 point for each effort they have here.',
    },
    frozen: {
      name: 'Frozen',
      text: 'Once all players have placed half of their effort, this setting will be replaced at random.',
    },
  };
}
