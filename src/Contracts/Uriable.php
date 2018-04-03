<?php

namespace Luclin\Contracts;

interface Uriable
{
    public static function getRootName(): string;
    public static function fromUri(string $uri): self;
    public function toUri(): string;
}