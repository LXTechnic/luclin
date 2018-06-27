<?php

namespace Luclin\Foundation;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel implements Contracts\Model
{

}