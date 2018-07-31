<?php

use \Illuminate\Database\Eloquent\ModelNotFoundException;

return [
    'server_error'  => [
        'num'       => 4300,            // 不设置的话需要以号码值作为键值
        'lvl'       => 'critical',      // 不设默认为 warning
        'exc'       => \RuntimeException::class,
    ],
    'unknown'       => 4399,
    'param_error'   => [
        'num'       => 4301,
        // 'msg'    => '参数错误', // 允许直接填写报错信息
        'exc'       => \InvalidArgumentException::class, // 不设默认LogicException
    ],
    'status_flow_error'   => [
        'num'       => 4350,
        'exc'       => \InvalidArgumentException::class,
    ],
    'model_type_attr_error'   => [
        'num'       => 4351,
        'exc'       => \UnexpectedValueException::class,
    ],
    'redis_model_not_found'   => [
        'num'       => 4771,
        'exc'       => ModelNotFoundException::class,
    ],
];