<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

function fwrite_stream( $stream, $string, $break )
{
	$chunk        = 1024;
	$bytesToWrite = strlen( $string );

	$index = 0;

	while ( $bytesToWrite > 0 )
	{
		$chunkSize = (int)min( $bytesToWrite, $chunk );
		$written   = fwrite( $stream, substr( $string, strlen( $string ) - $bytesToWrite, $chunkSize ) );

		$bytesToWrite -= $written;

		echo "Bytes written: {$written}\n";
		echo "Bytes remaining: {$bytesToWrite}\n";
		echo "Loop: {$index}\n";
		echo 'Interrupt: ' . ($break ? 'YES' : 'NO') . "\n";

		$index++;

//		if ( $break && $index > 5 )
//		{
//			echo "write interrupt\n";
//			break;
//		}
	}
	/*
	for ( $written = 0, $writtenMax = strlen( $string ); $written < $writtenMax; $written += $fwrite )
	{
		$fwrite = fwrite( $stream, substr( $string, $written ) );
		if ( $fwrite === false )
		{
			return $written;
		}
	}

	return $written;
	*/
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

fwrite_stream( $socket, $message2->toString(), false );

echo "√ Sent message 'This is a first test'\n";

fwrite_stream( $socket, $message1->toString(), true );

echo "√ Sent message 'This is a second test'\n";

var_dump( fclose( $socket ) );
