<?php

namespace Luclin\Foundation;

use Luclin\Abort;
use Luclin\Contracts\Protocol;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
use Log;
use Psr\Log\LoggerInterface;

/**
 * Laravel的违例处理并未对 \Error 类型做出处理。
 * 所以目前其他基础类错误并不能被该系统捕获，只能走 http code 500 来识别做默认处理。
 */
trait ExceptionHandlerTrait
{

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // return response()->json(['message' => $exception->getMessage()], 401);
        return $request->expectsJson()
                    ? response()->json(['message' => $exception->getMessage()], 401)
                    : redirect()->guest(route('login'));
    }

    protected function reportAbort(Abort $abort): void {
        if ($this->shouldntReport($abort)) {
            return;
        }

        if (method_exists($abort->getPrevious(), 'report')) {
            $abort->getPrevious()->report();
            return;
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (\Exception $exc) {
            // TODO: 暂未妥善处理
            throw $abort;
        }

        if ($abort->noticeOnly) {
            \luc\debug() && $logger->debug($abort->getMessage());
        } else {
            [$exc, $extra] = $abort();
            $level = $abort->level();
            $logger->$level(
                $abort->getMessage(),
                array_merge($this->context(), $extra, ['exception' => $exc]
            ));
        }
    }

    protected function renderException($request, \Throwable $exception): ?Response
    {
        try {
            if (!($exception instanceof Abort)) {
                if (\luc\debug()) {
                    return null;
                }
                // 在非debug模式下会将其他报错转义为一个默认报错
                $abort = \luc\raise('luclin.server_error', [], $exception);
            } else {
                $abort = $exception;
            }
            $response = \luc\protocol::abort($abort);
            return $response->send(...$abort->httpStatus());
        } catch (\Error $exc) {
            // TODO: 这里记录方案要完善
            Log::error($exc->getMessage(), $exc->getTrace());
            return response(['msg' => $exc->getMessage()], 500);
        } catch (\Exception $exc) {
            // 若在处理渲染报错时出错，记录错词日志并将错误交由框架处理
            $this->report($exc);
            return null;
        }
        return null;
    }
}