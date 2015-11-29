<?php

namespace Thruster\Component\Socket\Tests;

use Thruster\Component\EventLoop\EventLoop;
use Thruster\Component\Socket\Connection;
use Thruster\Component\Socket\Server;

/**
 * Class ConnectionTest
 *
 * @package Thruster\Component\Socket\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ConnectionTest extends TestCase
{
    public function testGetRemoteAddress()
    {
        $loop   = new EventLoop();
        $server = new Server($loop);
        $server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $server->getPort());

        $server->on('connection', function ($conn) use ($server) {
            $this->assertSame(
                $conn->parseAddress(stream_socket_get_name($server->getSocket(), false)),
                $conn->getRemoteAddress()
            );
        });
        $loop->tick();
    }

    public function dataRemoteAddress()
    {
        return [
            ['192.168.1.120', '192.168.1.120:12345'],
            ['9999:0000:aaaa:bbbb:cccc:dddd:eeee:ffff', '[9999:0000:aaaa:bbbb:cccc:dddd:eeee:ffff]:12345'],
            ['10.0.0.1', '10.0.0.1:80']
        ];
    }

    /**
     * @dataProvider dataRemoteAddress
     */
    public function testParseAddress($expected, $given)
    {
        $socket = fopen('php://temp', 'r');
        $loop   = $this->createLoopMock();

        $conn   = new Connection($socket, $loop);
        $result = $conn->parseAddress($given);

        $this->assertEquals($expected, $result);
    }

    private function createLoopMock()
    {
        return $this->getMock('Thruster\Component\EventLoop\EventLoopInterface');
    }
}
