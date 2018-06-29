<?php

namespace Luclin\Foundation;

use Illuminate\Auth\AuthenticationException;
use \Illuminate\Http\Response;

trait ExceptionHandlerTrait
{

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // return response()->json(['message' => $exception->getMessage()], 401);
        return $request->expectsJson()
                    ? response()->json(['message' => $exception->getMessage()], 401)
                    : redirect()->guest(route('login'));
    }

    protected function renderException($request, \Throwable $exception): ?Response
    {
        try {
        } catch (\Throwable $exc) {
            // 若在处理渲染报错时出错，记录错词日志并将错误交由框架处理
            $this->report($exc);
            return null;
        }
        // 如果是abort，取出原本的数据
        if ($exception instanceof Abort) {
            $info       = $exception->all();
            $exception  = $exception->getPrevious();
        } else {
            $info = [];
        }

        if (isset($info['httpCode']) && isset($info['httpCodeMessage'])) {
            abort($info['httpCode'],
                $info['httpCodeMessage'] ?? '',
                $info['headers'] ?? []);
        }

        // 逻辑错误走notice
        if ($exception instanceof \LogicException) {
            $response = new NoticeResponse($exception, $info);
            return response($response->toArray(), $info['httpCode'] ?? 403);
        }

        // 非逻辑错误但有60~90万错误号的给xhr抛error
        $code = $exception->getCode();
        if (($code >= 600000 || $code < 900000) && $request->ajax()) {
            $response = new ErrorResponse($exception, $info);
            return response($response->toArray(), $info['httpCode'] ?? 400);
        }

        // 线上环境遮避错误
        if (config('app.env') == 'production') {
            $response = new ErrorResponse(new \Exception(...helper::aborts(0)), $info);
            return response($response->toArray(), 500);
        }

        // 这段是TS原有逻辑，登录部分的错误转换
        // 暂时用不到
        // if ($exception instanceof JWTException) {
        //     abort($exception->getStatusCode(), $exception->getMessage());
        // }

        // 先这么处理，便于调试
        try {
            $message = \Combi\Helper::padding($exception->getMessage(), $info);
            if (isset($info['messageAddon'])) {
                $message .= ": {$info['messageAddon']}";
            }
            $class = get_class($exception);
            $exception = new $class($message, $exception->getCode(), $exception);
        } catch (\Throwable $e) {
            // do nothing..
        }
        return null;
    }
}