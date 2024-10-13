class ScoringTableBuilder {
  private mutableBoardState: MutableBoardState;
  private scoringDetail: TableScoring;

  // XXX: Depending on the ruleset, we may want to hide scoring for certain seats (e.g. the bot seats).

  private ATTRS = ['cha', 'con', 'dex', 'int', 'str', 'wis'];

  constructor(
    mutableBoardState: MutableBoardState,
    scoringDetail: TableScoring,
  ) {
    this.mutableBoardState = mutableBoardState;
    this.scoringDetail = scoringDetail;
  }

  public render(): string {
    const seatCount = Object.keys(this.scoringDetail.bySeat).length;

    console.log(this.scoringDetail);
    let s =
      '<div id="ewc_scoringarea" class="ewc_scoringarea pagesection">' +
      '<h3>Scoring</h3>' +
      '<table class="ewc_scoringtable">';

    s += '<thead><tr><th></th>';
    for (const seat of Object.values(this.mutableBoardState.seats)) {
      s += '<th colspan="2">' + seat.seatLabel + '</th>';
    }
    s += '</tr></thead>';

    s += '<tbody>';

    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * seatCount) +
      '">Attributes</td></tr>';

    for (const attr of this.ATTRS) {
      s +=
        '<tr class="subcategory">' +
        '<td>' +
        ' <div class="ewc_icon_attr icon_attr_' +
        attr +
        '_stat"></div></td>';

      // XXX: Ensure constant iteration order over seats?
      for (const seatId of Object.keys(this.mutableBoardState.seats)) {
        const seatScoring = this.scoringDetail.bySeat[+seatId]!;
        const statPoints = seatScoring.attributeData.points[attr];
        const points = seatScoring.attribute[attr]!;
        s += '<td>' + statPoints + '</td>';
        s += '<td>' + this.formatScore(points) + '</td>';
      }

      s += '</tr>';
    }

    // s +=
    //   '<tr class="subtotal">' +
    //   '<td>Attribute Total</td>' +
    //   '<td>5</td>' +
    //   '<td>4</td>' +
    //   '<td>0</td>' +
    //   '</tr>';

    // {sublocationIndex: Setting}
    // {sublocationIndex: Location}
    const settingByPos = [];
    for (const setting of Object.values(this.mutableBoardState.settings)) {
      settingByPos[setting.sublocationIndex] = setting;
    }

    const locationByPos = [];
    for (const location of Object.values(this.mutableBoardState.locations)) {
      locationByPos[location.sublocationIndex] = location;
    }

    console.log('**** scoring table');
    console.log(settingByPos);
    console.log(locationByPos);

    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * seatCount) +
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

      console.log(StaticDataSetlocs);
      console.log(locationMetadata);
      console.log(settingMetadata);

      s +=
        '<tr><td>' +
        settingMetadata.name +
        ' ' +
        locationMetadata.name +
        '</td>';

      for (const seatId of Object.keys(this.mutableBoardState.seats)) {
        const seatScoring = this.scoringDetail.bySeat[+seatId]!;

        s +=
          '<td colspan="2">' +
          this.formatScore(seatScoring.setting[location.id]!) +
          '</td>';
      }

      s += '</tr>';
    }

    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * seatCount) +
      '">Armor</td></tr>';

    s +=
      '<tr class="ewc_scoringtable_category"><td colspan="' +
      (1 + 2 * seatCount) +
      '">Items</td></tr>';

    s += '</tbody>';

    s += '</table>' + '</div>';
    return s;
  }

  private formatScore(points: number): string {
    if (points === 0) {
      return '&ndash;';
    }
    if (points > 0) {
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
