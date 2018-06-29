<?php

return [
    'param_error'   => [
        'msg'   => '参数错误', // 允许直接填写报错信息
        'exc'   => \InvalidArgumentException::class, // 不设默认LogicException
    ],
];