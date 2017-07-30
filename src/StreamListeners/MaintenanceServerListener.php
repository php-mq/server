<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Commands\CommandBuilder;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Events\Maintenance\ClientConnected;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Stream\Interfaces\TransfersData;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MaintenanceServerListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MaintenanceServerListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	/** @var PublishesEvents */
	private $eventBus;

	/** @var MaintenanceClientListener */
	private $maintenanceClientListener;

	public function __construct( PublishesEvents $eventBus )
	{
		$this->eventBus                  = $eventBus;
		$this->maintenanceClientListener = new MaintenanceClientListener( $this->eventBus, new CommandBuilder() );
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

		$loop->addReadStream( $clientStream, $this->maintenanceClientListener );

		$this->eventBus->publishEvent( new ClientConnected( $clientStream ) );
	}
}
