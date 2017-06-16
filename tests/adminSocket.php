<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

$serverSocket = stream_socket_server( 'tcp://127.0.0.1:9100' );

if ( false === $serverSocket )
{
	die( 'Failed to establish socket' );
}

if ( !stream_set_blocking( $serverSocket, false ) )
{
	die( 'Failed set to non-blocking' );
}

$clientSockets = [];

while ( true )
{
	usleep( 2000 );

	$reads   = $clientSockets;
	$reads[] = $serverSocket;
	$writes  = $excepts = null;

	if ( !stream_select( $reads, $writes, $excepts, 0 ) )
	{
		continue;
	}

	foreach ( $reads as $read )
	{
		if ( $serverSocket === $read )
		{
			$clientSocket = stream_socket_accept( $serverSocket, 0 );
			$name         = stream_socket_get_name( $clientSocket, true );

			echo "Client connected: {$name}\n";

			stream_set_blocking( $clientSocket, false );

			$clientSockets[] = $clientSocket;

			fwrite( $clientSocket, 'Hello!' );
			continue;
		}

		$buffer = fread( $read, 1024 );

		if ( empty( $buffer ) )
		{
			echo "Client disconnected.\n";

			$clientSockets = array_diff( $clientSockets, [ $read ] );
			continue;
		}

		echo "{$buffer}\n";
		fwrite( $read, 'OK' );
	}
}
