<?php

namespace Luclin2\Parts;

use Luclin\Abort;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property array $hooks
 * @property array $vars
 */
trait Hookable
{

    protected static function migrateUpHookable(Blueprint $table): void
    {
        $table->jsonb('hooks')->nullable();
        $table->jsonb('vars')->nullable();
    }

    protected static function migrateDownHookable(Blueprint $table): void
    {
        $table->dropColumn('hooks');
        $table->dropColumn('vars');
    }

    public function attachHooks(string $name, array $uriList) {
        $hooks = $this->hooks;
        $hooks[$name]   = array_merge($hooks[$name] ?? [], $uriList);
        $this->hooks    = $hooks;
    }

    public function takeHooks(string $name, array $context = [],
        array $vars = []): array
    {
        $context['_self'] = $this;
        $result = [];
        if ($uriList = $this->hooks[$name] ?? []) {
            foreach ($uriList as $uri) {
                $context['_result'] = $result;
                $this->vars && $vars = array_merge($this->vars, $vars);

                $vars && $uri = \luc\padding($uri, $vars);
                $hook = \luc\uri($uri, $context);
                $return   = $hook->raise();

                if ($hook->_return) {
                    $result[$hook->_return] = $return;
                } else {
                    $result[] = $return;
                }

                if ($return instanceof Abort) {
                    break;
                }
            }
        }
        return $result;
    }
}