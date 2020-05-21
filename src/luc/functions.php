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
use lastguest\Murmur;

function env(...$match): bool {
    return App::environment($match);
}

function du(...$arguments): void {
    \dump(...$arguments);
}

function debug(): bool {
    return config('app.debug');
}

function tryWhen(callable $try, callable $when, callable $throw, ...$arguments) {
    do {
        $exc = null;
        try {
            $result = $try(...$arguments);
        } catch (\Throwable $exc) {
            // do nothing..
        }
    } while($exc && $arguments = $when($exc, $arguments));
    if ($exc) {
        $throw($exc);
    }
    return $result;
}

function mqt(string $clientId = null, $mode = 'tcp', $connection = 'default'): Support\Mqt {
    $conf = config("mqt.$connection");
    if (!$conf) {
        throw new \RuntimeException("Mqtt connection [$connection] not found.");
    }

    $clientId && $conf['options']['clientId'] = $clientId;
    return Support\Mqt::instance("$clientId-$connection",
        $conf['brokers'][$mode], $conf['options'], $conf['auth']);
}

function packet(Luri $luri, array $context = [], $version = 1): Support\Mqt\Packet {
    $packet = new Support\Mqt\Packet($luri->toArray());
    $packet->setContext($context);
    $packet->version = $version;
    return $packet;
}

function mod(string $name, string $prefix = 'lumod:'): Module {
    return app("$prefix$name");
}

function mods(string $prefix = 'lumod:'): iterable {
    $fetch = function(): array {
        return $this->instances;
    };

    $length = strlen($prefix);
    foreach ($fetch->call(app()) as $key => $module) {
        if (strpos($key, $prefix) === 0) {
            yield substr($key, $length) => $module;
        }
    }
}

function transBit($data, int $fromBit, int $toBit) {
    $gmp  = gmp_init($data, $fromBit);
    if ($toBit == 10) {
        return gmp_intval($gmp);
    }
    return gmp_strval($gmp, $toBit);
}

function it2arr(iterable $it): array {
    $result = [];
    foreach ($it as $key => $value) {
        $result[$key] = $value;
    }
    return $result;
}

function hyphen2class(string $haystack): string {
    return str_replace(['_', '-'], '', \luc\pipe($haystack)
        ->ucwords('/_-')
        ->strtr('/', '\\')
        ());
}

function uri($url, ?array $context = [], $autoResolve = true) {
    if (is_array($url)) {
        $scheme = strstr($url[0], ':', true);
        $path   = substr($url[0], strlen($scheme) + 1);
        $luri = new Luri($scheme, $path, $url[1] ?? []);
    } else {
        $luri = Luri::createByUri($url);
    }
    return ($luri && $autoResolve) ? $luri->resolve($context)[0] : $luri;
}

function xheaders(bool $refresh = false): Protocol\XHeaders {
    $container = \app();
    $refresh && $container->forgetInstance('luclin.xheaders');
    return $container['luclin.xheaders'];
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
            $msg = \luc\__(substr_replace($error, '::aborts', $pos, 0), $extra);
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

function duQuery($query) {
    du($query->toSql());
    du($query->getBindings());
}

function suffix(string $subject, string $search = '.'): string {
    $pos = strrpos($subject, $search);
    return $pos !== false ? substr($subject, $pos + 1) : '';
}

function patchSet(array $oldSet, array $newSet): array {
    $remove = array_diff($oldSet, $newSet);
    $append = array_diff($newSet, $oldSet);
    return [$remove, $append];
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

function toLetters($number): string {
    return Support\Letter::encode($number);
}

function fromLetters($letters, bool $hex = false) {
    $hexed = Support\Letter::decode($letters);
    return $hex ? $hexed : hexdec($hexed);
}

function id(): string {
    $order  = gmp_init((int)(microtime(true) * 10));
    $id16   = gmp_strval($order, 16).str_replace('-', '', uuid_create());
    return gmp_strval(gmp_init('0x'.$id16, 16), 62);
}

// TODO: 定义version有效期到2149年 可通过version延长 version为62进制
function gid2(int $now = 0, string $shard = '000',
    int $randomBytes = 4, $version = '1'): string
{
    // 获取微秒时间戳
    !$now && $now = microtime(true);
    // 减去偏移量并精确到100微秒
    $now = ($now - 1580000000) * 10000;
    // 生成16进制时间戳
    $stamp = str_pad(dechex($now), 12, '0', \STR_PAD_LEFT);
    // 生成16进制随机数
    $random = bin2hex(openssl_random_pseudo_bytes($randomBytes));
    // 拼上16进制分片组成16进制字串
    $hex = "$stamp$shard$random";
    // 计算出62进制值
    $id = gmp_strval(gmp_init("0x$hex", 16), 62);
    // 确保 16 位
    if ($version) {
        return $version.substr(str_pad($id, 15, '0', \STR_PAD_LEFT), 0, 15);
    }

    // 不定义version则有效期超过4049年 但是当需要对数据做分割时会遇到麻烦
    return substr(str_pad($id, 16, '0', \STR_PAD_LEFT), 0, 16);
}

// TODO: sid不能做主键id，并且使用的时候结合其他字段条件进行筛选，仅作为次级数据排序之用
function sid2(string $gid, int $base, int $now = 0): string
{
    // 取gid作为前缀
    $prefix = substr($gid, 0, 8);

    // 获取秒时间戳
    !$now && $now = time();

    // 计算出时间偏移量实现排序
    // TODO: 偏移量限制在776.7天，超过后排序将轮回
    $time = abs($now - $base) % (67108863 - 1);

    // 生成16进制时间戳
    $stamp = str_pad(dechex($time), 7, '0', \STR_PAD_LEFT);
    // 生成16进制随机数
    $random = substr(bin2hex(openssl_random_pseudo_bytes(3)), 0, 5);
    // 组成16进制字串
    $hex = "$stamp$random";
    // 计算出62进制值
    $id = gmp_strval(gmp_init("0x$hex", 16), 62);
    // 确保 16 位
    return $prefix.substr(str_pad($id, 8, '0', \STR_PAD_LEFT), 0, 8);
}

function gid(int $now = 0, string $shard = '0000',
    int $randomBytes = 4, $version = '0'): string
{
    // 获取毫秒时间戳
    !$now && $now = microtime(true);
    // 减去偏移量并精确到毫秒
    $now = ($now - 1500000000) * 1000;
    // 生成16进制表达时间戳
    $stamp = str_pad(dechex($now), 10, '0', \STR_PAD_LEFT);
    // 生成16进制随机数
    $random = bin2hex(openssl_random_pseudo_bytes($randomBytes));
    // 拼上分片组成16进制字串
    $hex = "$stamp$shard$random";
    return $version.gmp_strval(gmp_init("0x$hex", 16), 62);
}

function sgid(int $now = 0, string $shard = '0',
    int $randomBytes = 5): string
{
    // 获取毫秒时间戳
    !$now && $now = time();
    // 减去偏移量并精确到毫秒
    $now = ceil(($now - 1500000000) / 10000);
    $hex = str_pad(dechex($now), 4, '0', \STR_PAD_LEFT).
        $shard.
        bin2hex(openssl_random_pseudo_bytes($randomBytes));
    return gmp_strval(gmp_init("0x$hex", 16), 62);
}

function parseGid(string $gid, int $realmBytes = 2, int $randomBytes = 4): array {
    $version    = $gid[0];
    $hexed      = gmp_strval(gmp_init($gid, 62), 16);
    $length     = strlen($hexed);

    $timestampLength = $length - 2 * $randomBytes - 2 * $realmBytes;
    $timeHex    = substr($hexed, 0, $timestampLength);
    $timestamp  = gmp_intval(gmp_init('0x'.$timeHex, 16)) / 1000;

    $realm      = substr($hexed, $timestampLength, 2 * $realmBytes);
    $random     = substr($hexed, $timestampLength + 2 * $realmBytes, 2 * $randomBytes);
    return [$version, $timestamp, $realm, $random];
}

function hash($value): string {
    return Murmur::hash3($value);
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
    if ($replace && $line && is_string($line)) {
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