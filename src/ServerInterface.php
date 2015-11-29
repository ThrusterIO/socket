<?php

namespace Thruster\Component\Socket;

use Thruster\Component\EventEmitter\EventEmitterInterface;

/**
 * Interface ServerInterface
 *
 * @package Thruster\Component\Socket
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface ServerInterface extends EventEmitterInterface
{
    /**
     * @param int    $port
     * @param string $host
     *
     * @return mixed
     */
    public function listen(int $port, string $host = '127.0.0.1');

    /**
     * @return int
     */
    public function getPort() : int;

    public function shutdown();
}
