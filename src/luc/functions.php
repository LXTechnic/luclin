<?php

namespace luc;

use Luclin\Abort;
use Luclin\Module;
use Luclin\Flow;
use Luclin\Luri;
use Luclin\Protocol;
use Luclin\Support;

use App;
use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Nette\Neon\Neon;

function env(...$match): bool {
    return App::environment($match);
}

function debug(): bool {
    return config('app.debug');
}

function mqt($connection = 'default'): Support\Mqt {
    $conf = config("mqt.$connection");
    if (!$conf) {
        throw new \RuntimeException("Mqtt connection [$connection] not found.");
    }

    return Support\Mqt::instance($connection,
        $conf['brokers'], $conf['options'], $conf['auth']);
}

function mod(string $name, string $prefix = 'lumod:'): Module {
    return app("$prefix$name");
}

function hyphen2class(string $haystack): string {
    return str_replace(['_', '-'], '', \luc\pipe($haystack)
        ->ucwords('/_-')
        ->strtr('/', '\\')
        ());
}

function uri($url, array $context = []) {
    if (is_array($url)) {
        $scheme = strstr($url[0], ':', true);
        $path   = substr($url[0], strlen($scheme) + 1);
        $luri = new Luri($scheme, $path, $url[1] ?? []);
    } else {
        $luri = Luri::createByUri($url);
    }
    return $luri ? $luri->resolve($context)[0] : null;
}

function ins(string $name, ...$extra) {
    $category = strstr($name, '.', true) ?: $name;
    $instance = \app("luclin.$name");
    if ($extra) {
        switch ($category) {
            case 'path':
                $instance .= '/'.implode('/', $extra);
                break;
        }
    }
    return $instance;
}

function xheaders(): Protocol\XHeaders {
    return ins('xheaders');
}

function flow($body): Flow {
    if (is_callable($body)) {
        return new Flow($body);
    }
    return Flow::instance($body);
}

function raise($error, array $extra = [], \Throwable $previous = null): Abort
{
    $params = [];
    if (is_string($error)) {
        if (!($conf = config("aborts.$error")) && !is_array($conf)) {
            throw new \UnexpectedValueException("Raise error config is not found.");
        }
        $num = $conf['num'] ?? $conf;
        if (isset($conf['msg'])) {
            $msg = \luc\padding($conf['msg'], $extra);
        } else {
            $pos = strpos($error, '.');
            $msg = \luc\__(substr_replace($error, '::aborts', $pos, 0), $extra);;
        }
        $exc = $conf['exc'] ?? \LogicException::class;
        $error = new $exc($msg, $num, $previous);

        $params[] = $error;
        $params[] = $extra;
        isset($conf['lvl']) && $params[] = $conf['lvl'];
    } else {
        $params[] = $error;
        $params[] = $extra;
    }
    $abort = new Abort(...$params);
    return $abort;
}

function pipe($handle, $agent = ''): Support\Pipe {
    return new Support\Pipe($handle, $agent);
}

function fs(): Filesystem {
    return new Filesystem();
}

function faker(): \Faker\Generator {
    return \Faker\Factory::create(config('app.faker_locale') ?: config('app.locale'));
}

function ndecode(string $data) {
    return Neon::decode($data);
}

function toArray(iterable $iterable,
    callable $filter = null): array
{
    $toArray = new Support\Recursive\ToArray($iterable, $filter);
    return $toArray();
}

function timer() {
    static $start = null;
    if (!$start) {
        $start = \PHP_VERSION_ID >= 70300 ? hrtime(true) : microtime(true);
        return $start;
    }
    $elapsed = \PHP_VERSION_ID >= 70300 ? hrtime(true) : microtime(true) - $start;
    $start   = null;
    return $elapsed;
}

// TODO: 对数组获取的兼容？
function __(string $key, array $replace = [],
    ?string $locale = null): ?string
{
    $translator = app('translator');
    $liner      = function() use ($key, $locale) {
        // from json
        $locale = $locale ?: $this->locale;
        $this->load('*', '*', $locale);
        $line = $this->loaded['*']['*'][$locale][$key] ?? null;
        if ($line) {
            return $line;
        }

        // from file
        [$namespace, $group, $item] = $this->parseKey($key);
        $this->load($namespace, $group, $locale);

        $line = Arr::get($this->loaded[$namespace][$group][$locale], $item);
        return $line;
    };

    $line = $liner->call($translator);
    if ($line && is_string($line)) {
        return padding($line, $replace);
    }
    return $line;
}

function padding(string $template, array $vars): ?string {
    // :abc + {{abc}} 相对单纯方案
    $result = preg_replace_callback(
        '/:([0-9]+)|:([A-Za-z_\@\$\.\-\~]+[0-9]*)|(\{\{)([A-Za-z0-9_\@\$\.\-\~\#\&]+)(\}\})/',
        function($matches) use ($vars) {
            $key = $matches[4] ?? $matches[2] ?? $matches[1];
            return $vars[$key] ?? $matches[0];
        }, $template);

    // 单纯 {{abc}}} 方案
    // $result = preg_replace_callback('/(\{\{)([A-Za-z0-9_\.\:\-\|\@\#\$\%\!\^\&\*\?\~]+)(\}\})/',
    //     function($matches) use ($vars) {
    //         return $vars[$matches[2]] ?? $matches[0];
    //     }, $template);

    // _:abc_ + {abc} 方案
    // $result = preg_replace_callback(
    //     '/(\s?):([A-Za-z0-9_\.]+)(\s?)|(\{?)(\{)([A-Za-z0-9_\.]+)(\})(\}?)/',
    //     function($matches) use ($vars) {
    //         $key = $matches[6] ?? $matches[2];
    //         if (isset($matches[8]) && $matches[4] == '{' && $matches[8] == '}') {
    //             return $matches[5].$matches[6].$matches[7];
    //         } elseif (isset($vars[$key])) {
    //             return isset($matches[8])
    //                 ? ($matches[4].$vars[$key].$matches[8])
    //                 : ($matches[1].$vars[$key].$matches[3]);
    //         } else {
    //             return $matches[0];
    //         }
    //     }, $template);

    // %abc% 方案
    // $result = preg_replace_callback('/(\%)([A-Za-z0-9_\.]+)(\%)|(\%\%)/',
    //     function($matches) use ($vars) {
    //         if (isset($matches[4])) {
    //             return '%';
    //         }

    //         if (isset($vars[$matches[2]])) {
    //             return $vars[$matches[2]];
    //         }
    //         return $matches[0];
    //     }, $template);

    return $result;
}