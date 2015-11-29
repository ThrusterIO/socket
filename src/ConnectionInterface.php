<?php

namespace Thruster\Component\Socket;

use Thruster\Component\Stream\ReadableStreamInterface;
use Thruster\Component\Stream\WritableStreamInterface;

/**
 * Interface ConnectionInterface
 *
 * @package Thruster\Component\Socket
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface ConnectionInterface extends ReadableStreamInterface, WritableStreamInterface
{
    /**
     * @return string
     */
    public function getRemoteAddress() : string;
}
