<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

$socket = stream_socket_client( 'tcp://127.0.0.1:9100', $errorNumber, $errorString, STREAM_CLIENT_CONNECT );

if ( false === $socket )
{
	die( 'Failed to establish socket.' );
}

if ( !stream_set_blocking( $socket, false ) )
{
	die( 'Failed to set socket non-blocking' );
}

$message1 = new MessageC2E( new QueueName( 'Test-Queue' ), 'This is a first test' );
$message2 = new MessageC2E( new QueueName( 'Test-Queue' ), 'This is a second test' );

fwrite( $socket, $message1->toString() );

echo "√ Sent message 'This is a first test'\n";

fwrite( $socket, $message2->toString() );

echo "√ Sent message 'This is a second test'\n";

$message1 = new MessageC2E( new QueueName( 'Example-Queue' ), 'This is a first test' );
$message2 = new MessageC2E( new QueueName( 'Example-Queue' ), 'This is a second test' );

fwrite( $socket, $message1->toString() );

echo "√ Sent message 'This is a first test'\n";

fwrite( $socket, $message2->toString() );

echo "√ Sent message 'This is a second test'\n";

fwrite( $socket, '' );
fclose( $socket );
