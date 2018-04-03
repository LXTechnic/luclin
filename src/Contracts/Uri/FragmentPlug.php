<?php

namespace Luclin\Contracts\Uri;

interface FragmentPlug
{
    public function __construct(string $value, \Luclin\Uri $parent);
    public function __invoke();
}