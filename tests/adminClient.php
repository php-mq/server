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

fwrite( $clientSocket, '12345678' );

fwrite( $clientSocket, '12345678' );

while ( true )
{
	$reads  = [ $clientSocket ];
	$writes = $excepts = null;

	if ( !stream_select( $reads, $writes, $excepts, 0 ) )
	{
		continue;
	}

	do
	{
		$buffer = fread( $clientSocket, 2 );

		if ( empty( $buffer ) )
		{
			echo "MessageQueueServer disconnected.\n";
			break 2;
		}

		echo $buffer, "\n";

		$meta = stream_get_meta_data( $clientSocket );
	}
	while ( $meta['unread_bytes'] > 0 );
}

fclose( $clientSocket );
