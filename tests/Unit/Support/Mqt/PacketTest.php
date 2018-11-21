<?php

namespace Luclin\Tests\Unit\Support\Mqt;

use Luclin\Support\Mqt;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PacketTest extends TestCase
{
    static $packed;
    static $packet;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPack()
    {
        $packet = new Mqt\Packet(\luc\uri('luc:test/packet-wrap?first=Merigold&ago=47', [
            'now'  => now()->toDateTimeString(),
        ], false)->toArray());
        static::$packed = $packet->pack();
        static::$packet = $packet;
        $this->assertNotEquals('', static::$packed);
    }

    public function testUnpack()
    {
        $packet = Mqt\Packet::unpack(static::$packed);
        $this->assertEquals($packet, static::$packet);
    }
}
