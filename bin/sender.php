<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ;

require __DIR__ . '/../vendor/autoload.php';

$socket = socket_create( AF_UNIX, SOCK_STREAM, 0 );
socket_connect( $socket, '/tmp/phpmq.sock' );
socket_set_nonblock( $socket );

sleep( 1 );

socket_write( $socket, 'Woot' );

sleep( 1 );

socket_write( $socket, 'Woot' );

sleep( 1 );

socket_shutdown( $socket );
socket_close( $socket );
