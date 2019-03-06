<?php

namespace Luclin\Foundation\Bus;

use Illuminate\Queue;

class QueueManager extends Queue\QueueManager
{
    public static function inherit(Queue\QueueManager $qm): self {
        $my = new self($qm->app);
        $my->connections    = $qm->connections;
        $my->connectors     = $qm->connectors;
        return $my;
    }

    protected function getConfig($name)
    {
        $config = parent::getConfig($name);
        if (!$config) {
            // 创建虚拟连接
            $config = parent::getConfig("vconn-$name");
            if ($config) {
                return $config;
            }

            $vconn = config("bus.vconn.$name");
            if (!$vconn) {
                return $config;
            }

            $base = parent::getConfig($vconn['base']);
            if (!$base) {
                return $base;
            }
            $config = array_replace_recursive($base, $vconn['modifies']);
            config(["queue.connections.vconn-$name" => $config]);
        }
        return $config;
    }

    public function debugConf($name) {
        return $this->getConfig($name);
    }

    public function debugConnectors() {
        return $this->connectors;
    }
}