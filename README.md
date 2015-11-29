# Socket Component

[![Latest Version](https://img.shields.io/github/release/ThrusterIO/socket.svg?style=flat-square)]
(https://github.com/ThrusterIO/socket/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)]
(LICENSE)
[![Build Status](https://img.shields.io/travis/ThrusterIO/socket.svg?style=flat-square)]
(https://travis-ci.org/ThrusterIO/socket)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/ThrusterIO/socket.svg?style=flat-square)]
(https://scrutinizer-ci.com/g/ThrusterIO/socket)
[![Quality Score](https://img.shields.io/scrutinizer/g/ThrusterIO/socket.svg?style=flat-square)]
(https://scrutinizer-ci.com/g/ThrusterIO/socket)
[![Total Downloads](https://img.shields.io/packagist/dt/thruster/socket.svg?style=flat-square)]
(https://packagist.org/packages/thruster/socket)

[![Email](https://img.shields.io/badge/email-team@thruster.io-blue.svg?style=flat-square)]
(mailto:team@thruster.io)

The Thruster Socket Component.

Library for building an evented socket server.

The socket component provides a more usable interface for a socket-layer
server or client based on the [`EventLoop`](https://github.com/ThrusterIO/event-loop)
and [`Stream`](https://github.com/ThrusterIO/stream) components.

## Server

The server can listen on a port and will emit a `connection` event whenever a
client connects.

## Connection

The `Connection` is a readable and writable [`Stream`](https://github.com/ThrusterIO/stream).
The incoming connection represents the server-side end of the connection.

It MUST NOT be used to represent an outgoing connection in a client-side context.
If you want to establish an outgoing connection,
use the [`SocketClient`](https://github.com/ThrusterIO/socket-client) component instead.


## Install

Via Composer

``` bash
$ composer require thruster/socket
```


## Usage

Here is a server that closes the connection if you send it anything.
```php
$loop = new EventLoop();

$socket = new Server($loop);
$socket->on('connection', function ($conn) {
    $conn->write("Hello world!\n");

    $conn->on('data', function ($data) use ($conn) {
        $conn->close();
    });
});
$socket->listen(1337);

$loop->run();
```    

You can change the host the socket is listening on through a second parameter 
provided to the listen method:

```php
$socket->listen(1337, '192.168.0.1');
```


## Testing

``` bash
$ composer test
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.


## License

Please see [License File](LICENSE) for more information.
