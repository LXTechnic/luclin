<?php

namespace Luclin\Support;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Luri;

use Mosquitto\Client;

class Mqt
{
    use Foundation\SingletonNamedTrait;

    public $defaultPacketVersion = 1;
    public $connected = false;

    protected $client;

    private $brokers;
    private $options = [
        'cliendId'  => null,
        'clean'     => true,
        'qos'       => 1,
        'retain'    => false,
        'keepAlive' => 0,
        'username'  => null,
        'password'  => null,
    ];
    private $auth;

    private $buffer = [];

    public function __construct(array $brokers, array $options = [], array $auth = [])
    {
        $this->brokers  = $brokers;
        $this->options  = array_merge($this->options, $options);
        $this->auth     = $auth;

        $cliendId = $this->options['cliendId'] ?: \luc\idgen::sortedUuid();
        $this->client   = new Client($cliendId, $this->options['clean']);
    }

    public function makeJwt($auth): string {
        $jwt = new JwtToken();
        $jwt->auth          = $auth;
        $jwt->id            = \luc\idgen::sorted36();
        $jwt->expireTime    = \luc\time::now()
            ->addDays($this->auth['jwt']['expire'])
            ->timestamp;
        $token = $jwt->make($this->auth['jwt']['secret']);

        return $token;
    }

    public function selectBroker(): string {
        return $this->brokers[array_rand($this->brokers)];
    }

    public function connect(array $options = []): self {
        $options = array_merge($this->options, $options);

        if ($options['username']) {
            $this->client->setCredentials($options['username'], $options['password']);
        }

        [$host, $port]  = explode(':', $this->selectBroker());
        $this->client->connect($host, $port, $this->options['keepAlive']);
        return $this;
    }

    public function send(string $content, string $topic): self {
        $this->buffer[$topic][] = $content;
        $this->flush($topic);
        return $this;
    }

    private function flush(string $topic = null): self {
        if (!$this->connected) {
            return $this;
        }
        if ($topic) {
            if (isset($this->buffer[$topic])) foreach ($this->buffer[$topic] as $content) {
                $this->client->publish($topic, $content,
                    $this->options['qos'], $this->options['retain']);
            }
        } else {
            foreach ($this->buffer as $topic => $contents) {
                foreach ($contents as $content) {
                    $this->client->publish($topic, $content,
                        $this->options['qos'], $this->options['retain']);
                }
            }
        }
        return $this;
    }

    public function once(): self {
        $this->client->onConnect(function() {
            $this->connected = true;
            $this->flush();
            $this->close();
        });
        $this->client->loopForever();
        return $this;
    }

    public function listen(): self {
        $this->client->onConnect(function() {
            $this->connected = true;
            $this->flush();
        });
        $this->client->loopForever();
        return $this;
    }

    private function prepare(): self {
        $this->client->onDisconnect(function() {
            $this->connected = false;
        });
        return $this;
    }

    public function close(): self {
        $this->client->disconnect();
        return $this;
    }

    public function makePacket($url, array $context = []): Mqt\Packet {
        if (is_array($url)) {
            $scheme = strstr($url[0], ':', true);
            $path   = substr($url[0], strlen($scheme) + 1);
            $luri = new Luri($scheme, $path, $url[1] ?? []);
        } else {
            $luri = Luri::createByUri($url);
        }

        $packet = new Mqt\Packet($luri->toArray(), $this->defaultPacketVersion);
        $packet->setContext($context);
        return $packet;
    }

}
