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
  static settingMetadata: any = {
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
}
