<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;

/**
 * Class MaintenanceServerListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MaintenanceServerListener extends AbstractStreamListener
{
	/** @var MaintenanceClientListener */
	private $maintenanceClientListener;

	public function __construct()
	{
		$this->maintenanceClientListener = new MaintenanceClientListener();
	}

	protected function handleStreamActivity( $stream, TracksStreams $loop ) : void
	{
		$this->handleNewClient( $stream, $loop );
	}

	private function handleNewClient( $stream, TracksStreams $loop ) : void
	{
		$clientStream = @stream_socket_accept( $stream );

		if ( false === $clientStream )
		{
			return;
		}

		if ( !stream_set_blocking( $clientStream, false ) )
		{
			return;
		}

		$this->logger->debug(
			'New maintenance client connected: {clientName}',
			[ 'clientName' => stream_socket_get_name( $clientStream, true ) ]
		);

		$this->maintenanceClientListener->setLogger( $this->logger );

		$loop->addReadStream( $clientStream, $this->maintenanceClientListener->getListener() );
	}
}
