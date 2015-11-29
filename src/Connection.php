<?php

namespace Thruster\Component\Socket;

use Thruster\Component\Stream\Stream;

/**
 * Class Connection
 *
 * @package Thruster\Component\Socket
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Connection extends Stream implements ConnectionInterface
{
    /**
     * @param resource $stream
     */
    public function handleData($stream)
    {
        $data = stream_socket_recvfrom($stream, $this->bufferSize);
        if ('' !== $data && false !== $data) {
            $this->emit('data', array($data, $this));
        }

        if ('' === $data || false === $data || !is_resource($stream) || feof($stream)) {
            $this->end();
        }
    }

    public function handleClose()
    {
        if (is_resource($this->stream)) {
            stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
            stream_set_blocking($this->stream, false);

            fclose($this->stream);
        }
    }

    public function getRemoteAddress() : string
    {
        return $this->parseAddress(stream_socket_get_name($this->stream, true));
    }

    public function parseAddress(string $address) : string
    {
        return trim(substr($address, 0, strrpos($address, ':')), '[]');
    }
}
