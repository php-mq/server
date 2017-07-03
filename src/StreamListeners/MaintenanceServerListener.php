<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MaintenanceServerListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MaintenanceServerListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	/** @var MaintenanceClientListener */
	private $maintenanceClientListener;

	public function __construct()
	{
		$this->maintenanceClientListener = new MaintenanceClientListener();
	}

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		$clientStream = $stream->acceptConnection();

		if ( null === $clientStream )
		{
			return;
		}

		$this->logger->debug(
			'New maintenance client connected: {clientId}',
			[ 'clientId' => $clientStream->getStreamId()->toString() ]
		);

		$this->maintenanceClientListener->setLogger( $this->logger );

		$loop->addStream( $clientStream, $this->maintenanceClientListener );
	}
}
