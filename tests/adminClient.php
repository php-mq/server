<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

$clientSocket = stream_socket_client( 'tcp://127.0.0.1:9100' );

if ( false === $clientSocket )
{
	die( 'Failed to establish client socket.' );
}

if ( !stream_set_blocking( $clientSocket, false ) )
{
	die( 'Failed to set client socket non-blocking.' );
}

fwrite( $clientSocket, 'This is a test' );

while ( true )
{
	$reads  = [ $clientSocket ];
	$writes = $excepts = null;

	if ( !stream_select( $reads, $writes, $excepts, 0 ) )
	{
		continue;
	}

	$buffer = fread( $clientSocket, 1024 );

	if ( empty( $buffer ) )
	{
		echo "Server disconnected.\n";
		break;
	}

	echo $buffer;
}

fclose( $clientSocket );
