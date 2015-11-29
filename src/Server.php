<?php

namespace Thruster\Component\Socket;

use Thruster\Component\EventEmitter\EventEmitterTrait;
use Thruster\Component\EventLoop\EventLoopInterface;
use Thruster\Component\Socket\Exception\ConnectionException;

/**
 * Class Server
 *
 * @package Thruster\Component\Socket
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Server implements ServerInterface
{
    use EventEmitterTrait;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var EventLoopInterface
     */
    protected $loop;

    public function __construct(EventLoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * {@inheritDoc}
     */
    public function listen(int $port, string $host = '127.0.0.1')
    {
        if (strpos($host, ':') !== false) {
            // enclose IPv6 addresses in square brackets before appending port
            $host = '[' . $host . ']';
        }

        $this->socket = @stream_socket_server("tcp://$host:$port", $errno, $errstr);

        if (false === $this->socket) {
            $message = "Could not bind to tcp://$host:$port: $errstr";

            throw new ConnectionException($message, $errno);
        }

        stream_set_blocking($this->socket, 0);

        $this->loop->addReadStream($this->socket, function ($master) {
            $newSocket = stream_socket_accept($master);

            if (false === $newSocket) {
                $this->emit('error', [new \RuntimeException('Error accepting new connection')]);

                return;
            }

            $this->handleConnection($newSocket);
        });
    }

    public function handleConnection($socket)
    {
        stream_set_blocking($socket, 0);

        $client = $this->createConnection($socket);

        $this->emit('connection', [$client]);
    }

    public function getPort() : int
    {
        $name = stream_socket_get_name($this->socket, false);

        return (int)substr(strrchr($name, ':'), 1);
    }

    public function shutdown()
    {
        $this->loop->removeStream($this->socket);

        fclose($this->socket);

        $this->removeListeners();
    }

    protected function createConnection($socket) : Connection
    {
        return new Connection($socket, $this->loop);
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }
}
