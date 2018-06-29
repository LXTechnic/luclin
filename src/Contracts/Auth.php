<?php

namespace Luclin\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface Auth extends Authenticatable
{
    public function model();

    public function id();

    public function getAuthExtra($key = null);

    public function setAuthExtra(array $extra);
}