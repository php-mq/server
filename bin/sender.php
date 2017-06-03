<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

$socket = socket_create( AF_UNIX, SOCK_STREAM, 0 );
socket_connect( $socket, '/tmp/phpmq.server.sock' );
socket_set_nonblock( $socket );

$message1 = new MessageC2E( new QueueName( 'Test-Queue' ), 'This is a first test' );
$message2 = new MessageC2E( new QueueName( 'Test-Queue' ), 'This is a second test' );

socket_write( $socket, $message1->toString() );

echo "√ Sent message 'This is a first test'\n";

socket_write( $socket, $message2->toString() );

echo "√ Sent message 'This is a second test'\n";

$message1 = new MessageC2E( new QueueName( 'Example-Queue' ), 'This is a first test' );
$message2 = new MessageC2E( new QueueName( 'Example-Queue' ), 'This is a second test' );

socket_write( $socket, $message1->toString() );

echo "√ Sent message 'This is a first test'\n";

socket_write( $socket, $message2->toString() );

echo "√ Sent message 'This is a second test'\n";

socket_close( $socket );
