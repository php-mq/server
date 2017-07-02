<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

function fwrite_stream( $fp, $string )
{
	for ( $written = 0, $writtenMax = strlen( $string ); $written < $writtenMax; $written += $fwrite )
	{
		$fwrite = fwrite( $fp, substr( $string, $written ) );
		if ( $fwrite === false )
		{
			return $written;
		}
	}

	return $written;
}

$socket = @stream_socket_client( 'tcp://127.0.0.1:9100', $errorNumber, $errorString, STREAM_CLIENT_CONNECT );

if ( false === $socket )
{
	die( 'Failed to establish socket.' );
}

if ( !stream_set_blocking( $socket, false ) )
{
	die( 'Failed to set socket non-blocking' );
}

echo 'Client ID: ' . stream_socket_get_name( $socket, false ) . "\n";

$fileContent = file_get_contents( __DIR__ . '/../tests/Unit/Fixtures/test.jpg' );

$message1 = new MessageC2E( new QueueName( $argv[1] ), $fileContent );
$message2 = new MessageC2E( new QueueName( $argv[1] ), 'This is a second test' );

fwrite_stream( $socket, $message1->toString() );

echo "√ Sent message 'This is a first test'\n";

fwrite_stream( $socket, $message2->toString() );

echo "√ Sent message 'This is a second test'\n";

fclose( $socket );
