<?php declare(strict_types=1);

namespace WcLib;

abstract class PlayerBase
{
  protected string $id_;
  protected int $no_;
  protected string $color_;
  protected string $name_;

  /**
    @template PlayerT of PlayerBase
    @param class-string<PlayerT> $PlayerT
    @param ?string[] $row
    @return ?PlayerT

    // XXX:
    @phan-suppress PhanTypeExpectedObjectPropAccess
  */
  public static function fromRowBase(string $PlayerT, $row)
  {
    if ($row === null) {
      return null;
    }

    $that = new $PlayerT();

    $that->id_ = $row['player_id'];
    $that->no_ = intval($row['player_no']);
    $that->color_ = $row['player_color'];
    $that->name_ = $row['player_name'];

    return $that;
  }

  public function renderForClient() {
    return [
      'id' => $this->id_,
      'no' => $this->no_,
      'name' => $this->name_,
      'color' => $this->color_,
    ];
  }

  public function id(): string
  {
    return $this->id_;
  }

  public function no(): int {
    return $this->no_;
  }

  public function color(): string {
    return $this->color_;
  }

  public function name(): string {
    return $this->name_;
  }
}
