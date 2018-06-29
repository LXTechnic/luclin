<?php

return function() {
    return [
        // 'routes'        => [
        //     $this->module()->path('routes.php'),
        // ],
        'migrations'    => [
            $this->module()->path('database', 'migrations'),
        ],
        'config'        => [
            $this->module()->path('config', 'protocol.php') => 'protocol',
            $this->module()->path('config', 'module.php')   => static::$moduleName,
            $this->module()->path('config', 'errors.php')
                => 'errors.'.static::$moduleName,
        ],
        'lang'          => [
            $this->module()->path('resources', 'lang') => static::$moduleName,
        ],
        'views'         => [
            $this->module()->path('resources', 'views') => static::$moduleName,
        ],
        'commands'      => [
            'Luclin\\Commands' => $this->module()->path('src', 'Commands'),
        ],
        // 'assets'        => [
        //     $this->module()->path('assets') => public_path('vender/'.static::$moduleName),
        // ],
    ];
};