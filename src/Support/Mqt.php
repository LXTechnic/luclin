<?php

namespace Luclin\Support;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Luri;

use Mosquitto\Client;
use Mosquitto\Message;

class Mqt
{
    use Foundation\SingletonNamedTrait;

    public $connected = false;

    protected $client;

    private $brokers;
    private $options = [
        'cliendId'  => null,
        'clean'     => true,
        'qos'       => 1,
        'retain'    => false,
        'keepAlive' => 15,
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
        $this->prepare();
    }

    public function makeJwt($auth, $permanently = false): string {
        $jwt = new JwtToken();
        $jwt->auth          = $auth;
        $jwt->id            = \luc\idgen::sorted36();

        $expire = $permanently ? (365 * 4 + 1) :
            ($this->auth['jwt']['expire'] ?? (365 * 4 + 1));
        $jwt->expireTime    = \luc\time::now()
            ->addDays($expire ?: (365 * 4 + 1))
            ->timestamp;
        $token = $jwt->make($this->auth['jwt']['secret']);

        return $token;
    }

    public function clientId() {
        return $this->options['clientId'];
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

    public function sub(string $topic): self {
        $this->client->subscribe($topic, $this->options['qos']);
        return $this;
    }

    private function flush(string $topic = null): self {
        if (!$this->connected) {
            return $this;
        }
        if ($topic) {
            $hasExc = false;
            try {
                if (isset($this->buffer[$topic])) foreach ($this->buffer[$topic] as $content) {
                    $this->client->publish($topic, $content,
                        $this->options['qos'], $this->options['retain']);
                }
            } catch (\Mosquitto\Exception $exc) {
                $hasExc = true;
            }
            !$hasExc && $this->buffer[$topic] = [];
        } else {
            $hasExc = false;
            try {
                foreach ($this->buffer as $topic => $contents) {
                    foreach ($contents as $content) {
                        $this->client->publish($topic, $content,
                            $this->options['qos'], $this->options['retain']);
                    }
                }
            } catch (\Mosquitto\Exception $exc) {
                $hasExc = true;
            }
            !$hasExc && $this->buffer = [];
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

    public function listen(callable $func = null): self {
        $func && $this->client->onMessage(function(Message $message) use ($func) {
            $func($message->payload, $message->topic, $message->mid, $message->retain);
        });

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
        $this->client->exitLoop();
        return $this;
    }

}
