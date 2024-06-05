<?php declare(strict_types=1);

namespace EffortlessWC;

class WorldImpl implements World
{
  private $table_;

  function __construct($table)
  {
    $this->table_ = $table;
  }
}
