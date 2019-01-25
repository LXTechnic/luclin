<?php

namespace Luclin\Cabin\Events;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Meta;

class DBConnected
{
    public $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
}