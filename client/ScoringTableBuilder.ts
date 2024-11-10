class ScoringTableBuilder {
  private mutableBoardState: MutableBoardState;
  private scoringDetail: TableScoring;

  private ATTRS = ['cha', 'con', 'dex', 'int', 'str', 'wis'];
  private ARMOR_SETS: Record<string, string> = {
    assasin: 'Assassin',
    leather: 'Leather',
    mage: 'Mage',
    obsidian: 'Obsidian',
    plate: 'Plate',
    scale: 'Scale',
  };

  constructor(
    mutableBoardState: MutableBoardState,
    scoringDetail: TableScoring,
  ) {
    this.mutableBoardState = mutableBoardState;
    this.scoringDetail = scoringDetail;
  }

  public render(): string {
    // Get a fixed order to render the seats in.  Filtering out unscored seats (e.g. bot seats) is done on the server
    // side.  This will be a subset of the keys in `this.mutableBoardState.seats`.
    const scoringSeats: number[] = Object.keys(this.scoringDetail.bySeat).map(
      (key: string): number => {
        return +key;
      },
    );

    console.log(this.scoringDetail);
    let s =
      '<div id="ewc_scoringarea" class="ewc_scoringarea pagesection">' +
      '<h3>Scoring</h3>' +
      '<table class="ewc_scoringtable">';

    /* Seat labels */
    s += '<thead><tr><th></th>';
    for (const seatId of scoringSeats) {
      const seat = this.mutableBoardState.seats[seatId]!;
      s += '<th colspan="2">' + seat.seatLabel + '</th>';
    }
    s += '</tr></thead>';

    s += '<tbody>';

    /* Attributes */
    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * scoringSeats.length) +
      '">Attributes</td></tr>';

    const attrSubtotals = new Map<number, number>();
    for (const attr of this.ATTRS) {
      s +=
        '<tr>' +
        '<td>' +
        ' <div class="ewc_icon_attr icon_attr_' +
        attr +
        '_stat"></div></td>';

      // XXX: Ensure constant iteration order over seats?
      for (const seatId of scoringSeats) {
        const seatScoring = this.scoringDetail.bySeat[+seatId]!;
        const statPoints = seatScoring.attributeData.points[attr];
        const points = seatScoring.attribute[attr]!;
        s += '<td class="ewc_scoringtable_splita">' + statPoints + '</td>';
        s +=
          '<td class="ewc_scoringtable_splitb">' +
          this.formatScore(points) +
          '</td>';

        attrSubtotals.set(+seatId, (attrSubtotals.get(+seatId) ?? 0) + points);
      }

      s += '</tr>';
    }
    s += this.formatSubtotals(scoringSeats, attrSubtotals);

    /* Settings */

    // {sublocationIndex: Setting}
    const settingByPos = [];
    for (const setting of Object.values(this.mutableBoardState.settings)) {
      settingByPos[setting.sublocationIndex] = setting;
    }

    // {sublocationIndex: Location}
    const locationByPos = [];
    for (const location of Object.values(this.mutableBoardState.locations)) {
      locationByPos[location.sublocationIndex] = location;
    }

    // console.log('**** scoring table');
    // console.log(settingByPos);
    // console.log(locationByPos);

    const settingSubtotals = new Map<number, number>();
    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * scoringSeats.length) +
      '">Settings</td></tr>';
    for (let i = 0; i < 6; ++i) {
      const settingKey: string = this.extractSetlocKey(
        settingByPos[i]!.cardType!,
      )!;
      const location = locationByPos[i]!;
      const locationKey: string = this.extractSetlocKey(location.cardType!)!;

      // console.log('** setloc ', i);
      // console.log(location);
      // console.log(setting);

      const locationMetadata = StaticDataSetlocs.locationMetadata[locationKey];
      const settingMetadata = StaticDataSetlocs.settingMetadata[settingKey];

      // console.log(StaticDataSetlocs);
      // console.log(locationMetadata);
      // console.log(settingMetadata);

      s +=
        '<tr><td>' +
        settingMetadata.name +
        ' ' +
        locationMetadata.name +
        '</td>';

      for (const seatId of scoringSeats) {
        const seatScoring = this.scoringDetail.bySeat[+seatId]!;
        const points = seatScoring.setting[location.id]!;
        s += '<td colspan="2">' + this.formatScore(points) + '</td>';

        settingSubtotals.set(
          +seatId,
          (attrSubtotals.get(+seatId) ?? 0) + points,
        );
      }

      s += '</tr>';
    }
    s += this.formatSubtotals(scoringSeats, settingSubtotals);

    /* Armor */
    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * scoringSeats.length) +
      '">Armor</td></tr><tr><td></td>';
    const armorSubtotals = new Map<number, number>();
    for (const seatId of scoringSeats) {
      const seatScoring = this.scoringDetail.bySeat[+seatId]!;

      armorSubtotals.set(seatId, 0);
      s += '<td colspan="2">';
      for (const [setName, points] of Object.entries(seatScoring.armor)) {
        s +=
          this.ARMOR_SETS[setName] + ' ' + this.formatScore(points) + '<br />';
      }
      if (seatScoring.armor.length === 0) {
        s += 'No armor.<br />';
      }
      s += '</td>';
    }
    s += '</tr>';
    s += this.formatSubtotals(scoringSeats, armorSubtotals);

    /* Items */
    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * scoringSeats.length) +
      '">Items</td></tr><tr><td></td>';
    const itemSubtotals = new Map<number, number>();
    for (const seatId of scoringSeats) {
      itemSubtotals.set(seatId, 0);
      const seatScoring = this.scoringDetail.bySeat[+seatId]!;

      s += '<td colspan="2">';
      for (const [itemId, points] of Object.entries(seatScoring.item)) {
        const card = seatScoring.itemCards[+itemId]!;
        const cardMetadata = StaticDataCards.cardMetadata[card.cardType!];

        if (points === 0) {
          s +=
            '<span class="ewc_scoringtable_unusable">' +
            cardMetadata.title +
            '</span><br />';
        } else {
          s += cardMetadata.title + ' ' + this.formatScore(points) + '<br />';
        }
      }
      // XXX: Not clear to me why the `Object.keys()` call is necessary here.
      if (Object.keys(seatScoring.item).length === 0) {
        s += 'No items.<br />';
      }
      s += '</td>';
    }
    s += '</tr>';
    s += this.formatSubtotals(scoringSeats, itemSubtotals);

    /* Overall totals */
    s += '<tr class="ewc_scoringtable_total">' + '<td></td>';
    for (const seatId of scoringSeats) {
      const seatScoring = this.scoringDetail.bySeat[+seatId]!;

      s += '<td colspan="2">' + this.formatScore(seatScoring.total) + '</td>';
    }
    s += '</tr>';

    s += '</tbody>';

    s += '</table>' + '</div>';
    return s;
  }

  private formatSubtotals(
    scoringSeats: number[],
    subtotals: Map<number, number>,
  ): string {
    let s = '';
    s += '<tr class="ewc_scoringtable_subtotal">' + '<td></td>';
    for (const seatId of scoringSeats) {
      s +=
        '<td colspan="2">' +
        this.formatScore(subtotals.get(+seatId)!, /*showZero=*/ true) +
        '</td>';
    }
    s += '</tr>';
    return s;
  }

  private formatScore(points: number, showZero: boolean = false): string {
    if (points === 0 && showZero !== true) {
      return '&ndash;';
    }
    if (points >= 0) {
      return points + '<div class="ewc_icon_points points_pos"></div>';
    }
    return -1 * points + '<div class="ewc_icon_points points_neg"></div>';
  }

  // XXX: Move to shared utils.
  private extractSetlocKey(s: string): string | null {
    const k = s.split(':')[1] ?? null;
    console.log('*** s/k =', s, k);
    return k;
  }
}
