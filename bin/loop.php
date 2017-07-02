<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Endpoint\Loop;

require __DIR__ . '/../vendor/autoload.php';

$messageQueueServer = @stream_socket_server(
	'tcp://127.0.0.1:9100',
	$errorNumber,
	$errorString,
	STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
);

stream_set_blocking( $messageQueueServer, false );

//$maintenanceServer = @stream_socket_server(
//	'tcp://127.0.0.1:9101',
//	$errorNumber,
//	$errorString,
//	STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
//);

$clientReadHandler = function ( $stream, Loop $loop )
{
	do
	{
		$bytes = (string)@fread( $stream, 1024 );

		if ( !$bytes )
		{
			echo 'Client disconnected: ' . ((int)$stream) . "\n";

			$loop->removeStream( $stream );
			break;
		}

		$metaData = stream_get_meta_data( $stream );
	}
	while ( $metaData['unread_bytes'] > 0 );
};

$loop = new Loop();
$loop->addReadStream(
	$messageQueueServer,
	function ( $stream, Loop $loop ) use ( $clientReadHandler )
	{
		$clientStream = stream_socket_accept( $stream );
		if ( is_resource( $clientStream ) )
		{
			stream_set_blocking( $clientStream, false );

			echo 'New client connected: ' . ((int)$clientStream) . "\n";

			$loop->addReadStream( $clientStream, $clientReadHandler );
		}
	}
);

$loop->start();
