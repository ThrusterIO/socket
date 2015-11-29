<?php

namespace Thruster\Component\Socket\Tests;

use Thruster\Component\EventLoop\EventLoop;
use Thruster\Component\EventLoop\EventLoopInterface;
use Thruster\Component\Socket\Server;

/**
 * Class ServerTest
 *
 * @package Thruster\Component\Socket\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ServerTest extends TestCase
{
    /**
     * @var EventLoopInterface
     */
    protected $loop;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var int
     */
    protected $port;

    protected function createLoop()
    {
        return new EventLoop();
    }

    public function setUp()
    {
        $this->loop   = $this->createLoop();
        $this->server = new Server($this->loop);
    }

    public function getPort()
    {
        return $this->server->getPort();
    }

    public function testIpv6Connection()
    {
        $this->server->listen(0, '::1');

        $client = stream_socket_client('tcp://[::1]:' . $this->getPort());

        $this->server->on('connection', $this->expectCallableOnce());
        $this->loop->tick();
    }

    public function testConnection()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());

        $this->server->on('connection', $this->expectCallableOnce());
        $this->loop->tick();
    }

    public function testConnectionWithManyClients()
    {
        $this->server->listen(0);

        $client1 = stream_socket_client('tcp://localhost:' . $this->getPort());
        $client2 = stream_socket_client('tcp://localhost:' . $this->getPort());
        $client3 = stream_socket_client('tcp://localhost:' . $this->getPort());

        $this->server->on('connection', $this->expectCallableExactly(3));
        $this->loop->tick();
        $this->loop->tick();
        $this->loop->tick();
    }

    public function testDataWithNoData()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());

        $mock = $this->expectCallableNever();

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('data', $mock);
        });
        $this->loop->tick();
        $this->loop->tick();
    }

    public function testData()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());

        fwrite($client, "foo\n");

        $mock = $this->expectCallableOnceWith("foo\n");

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('data', $mock);
        });
        $this->loop->tick();
        $this->loop->tick();
    }

    public function testDataSentFromPy()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());
        fwrite($client, "foo\n");
        stream_socket_shutdown($client, STREAM_SHUT_WR);

        $mock = $this->expectCallableOnceWith("foo\n");

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('data', $mock);
        });
        $this->loop->tick();
        $this->loop->tick();
    }

    public function testFragmentedMessage()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());

        fwrite($client, "Hello World!\n");

        $mock = $this->expectCallableOnceWith("He");

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->setBufferSize(2);
            $conn->on('data', $mock);
        });
        $this->loop->tick();
        $this->loop->tick();
    }

    public function testDisconnectWithoutDisconnect()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());

        $mock = $this->expectCallableNever();

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('end', $mock);
        });
        $this->loop->tick();
        $this->loop->tick();
    }

    public function testDisconnect()
    {
        $this->server->listen(0);

        $client = stream_socket_client('tcp://localhost:' . $this->getPort());

        fclose($client);

        $mock = $this->expectCallableOnce();

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('end', $mock);
        });

        $this->loop->tick();
        $this->loop->tick();
    }

    /**
     * @expectedException \Thruster\Component\Socket\Exception\ConnectionException
     * @expectedExceptionMessageRegExp /Could not bind to tcp:\/\/127\.0\.0\.1:\d{1,6}: Address already in use/
     */
    public function testAlreadyListening()
    {
        $this->server->listen(0);

        $server = new Server($this->loop);
        $server->listen($this->getPort());
    }

    public function tearDown()
    {
        if ($this->server) {
            $this->server->shutdown();
        }
    }
}
