<?php

return function() {
    return [
        'routes'        => [
            $this->module()->path('routes.php'),
        ],
        'migrations'    => [
            $this->module()->path('database', 'migrations'),
        ],
        'config'        => [
            $this->module()->path('config', 'module.php') => static::$moduleName,
            $this->module()->path('config', 'aborts.php')
                => 'aborts.'.static::$moduleName,
            $this->module()->path('config', 'database', 'connections.php')
                => 'database.connections',
        ],
        'lang'          => [
            $this->module()->path('resources', 'lang') => static::$moduleName,
        ],
        'views'         => [
            $this->module()->path('resources', 'views') => static::$moduleName,
        ],
        'commands'      => [
            ':MyModule-ASED\\Commands'
                => $this->module()->path('src', 'Commands'),
        ],
    ];
};