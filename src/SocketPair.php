<?php

namespace Thruster\Component\Socket;

use Thruster\Component\EventEmitter\EventEmitterTrait;
use Thruster\Component\EventLoop\EventLoopInterface;
use Thruster\Component\Socket\Exception\ConnectionException;

/**
 * Class SocketPair
 *
 * @package Thruster\Component\Socket
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SocketPair
{
    const LEFT_SIDE = true;
    const RIGHT_SIDE = false;

    /**
     * @var EventLoopInterface
     */
    protected $loop;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $sockets;

    /**
     * @var bool
     */
    protected $mode;

    public function __construct(EventLoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function create()
    {
        $this->sockets = @stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        if (false === $this->sockets) {
            throw new ConnectionException('Could not create socket pair');
        }
    }

    public function useLeft() : Connection
    {
        list($left, $right) = $this->sockets;

        fclose($right);

        $this->mode = self::LEFT_SIDE;
        $this->connection = new Connection($left, $this->loop);

        return $this->connection;
    }

    public function useRight() : Connection
    {
        list($left, $right) = $this->sockets;

        fclose($left);

        $this->mode = self::RIGHT_SIDE;
        $this->connection = new Connection($right, $this->loop);

        return $this->connection;
    }

    public function shutdown()
    {
        $this->connection->close();
    }

    public function getConnection() : Connection
    {
        return $this->connection;
    }
}
