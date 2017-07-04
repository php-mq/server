<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Maintenance;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Events\Maintenance\ClientConnected;
use PHPMQ\Server\Events\Maintenance\ClientDisconnected;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;

/**
 * Class ClientConnectionEventHandler
 * @package PHPMQ\Server\EventHandlers\Maintenance
 */
final class ClientConnectionEventHandler extends AbstractEventHandler
{
	/** @var PreparesOutputForCli */
	private $cliWriter;

	public function __construct( PreparesOutputForCli $cliWriter )
	{
		$this->cliWriter = $cliWriter;
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientConnected::class,
			ClientDisconnected::class,
		];
	}

	protected function whenClientConnected( ClientConnected $event ) : void
	{
		$stream = $event->getStream();

		$this->logger->debug( 'New maintenance client connected: ' . $stream->getStreamId() );

		$this->cliWriter->clearScreen( 'Welcome!' )->writeLn( 'Type "help" to list available commands.' );

		$stream->write( $this->cliWriter->getInteractiveOutput() );
	}

	protected function whenClientDisconnected( ClientDisconnected $event ) : void
	{
		$stream = $event->getStream();

		$this->logger->debug( 'Maintenance client disconnected: ' . $stream->getStreamId() );
	}
}
