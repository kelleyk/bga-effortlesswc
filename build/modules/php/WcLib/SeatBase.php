<?php declare(strict_types=1);

namespace WcLib;

abstract class SeatBase
{
  protected int $id_;
  protected ?string $player_id_;
  protected string $seat_color_;
  protected string $seat_label_;

  /**
    @template SeatT of SeatBase
    @param class-string<SeatT> $SeatT
    @param ?string[] $row
    @return ?SeatT

    // XXX:
    @phan-suppress PhanTypeExpectedObjectPropAccess
  */
  public static function fromRowBase(string $SeatT, $row)
  {
    if ($row === null) {
      return null;
    }

    $that = new $SeatT();

    $that->id_ = intval($row['id']);
    $that->player_id_ = $row['player_id'];
    $that->seat_color_ = $row['seat_color'];
    $that->seat_label_ = $row['seat_label'];

    return $that;
  }

  public function renderForClientBase() {
    return [
      'id' => $this->id_,
      'playerId' => $this->player_id_,
      'seatColor' => $this->seat_color_,
      'seatLabel' => $this->seat_label_,
    ];
  }

  public function id(): int
  {
    return $this->id_;
  }

  public function player_id(): ?string {
    return $this->player_id_;
  }

  public function seat_color(): string {
    return $this->seat_color_;
  }

  public function seat_label(): string {
    return $this->seat_label_;
  }
}
