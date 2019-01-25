<?php

namespace Luclin\Cabin\Foundation;

use Luclin\Contracts;
use Luclin\Cabin;
use Luclin\Cabin\Events;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Connectors;
use Illuminate\Database\Connection;

use DB;

class ConnectionFactory extends Connectors\ConnectionFactory
{
    protected function createConnection($driver, $connection, $database,
        $prefix = '', array $config = [])
    {
        if ($resolver = Connection::getResolver($driver)) {
            return $resolver($connection, $database, $prefix, $config);
        }

        $conn = parent::createConnection($driver, $connection, $database, $prefix, $config);

        \event(new Events\DBConnected($conn));

        return $conn;
    }
}