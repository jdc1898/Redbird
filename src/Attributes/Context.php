<?php

namespace Fullstack\Redbird\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Context
{
  public function __construct(
    public string $description,
    public ?string $file = null,
  ) {}
}
