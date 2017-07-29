<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Interfaces\BuildsCommands;
use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\ClearScreenCommand;
use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Commands\Exceptions\UnknownCommandException;
use PHPMQ\Server\Commands\FlushAllQueuesCommand;
use PHPMQ\Server\Commands\FlushQueueCommand;
use PHPMQ\Server\Commands\HelpCommand;
use PHPMQ\Server\Commands\QuitRefreshCommand;
use PHPMQ\Server\Commands\SearchQueueCommand;
use PHPMQ\Server\Commands\ShowQueueCommand;
use PHPMQ\Server\Commands\StartMonitorCommand;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Events\Maintenance\ClientDisconnected;
use PHPMQ\Server\Events\Maintenance\ClientRequestedClearScreen;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingAllQueues;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingQueue;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueSearch;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Streams\Constants\ChunkSize;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MaintenanceClientListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MaintenanceClientListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	/** @var PublishesEvents */
	private $eventBus;

	/** @var BuildsCommands */
	private $commandBuilder;

	public function __construct( PublishesEvents $eventBus, BuildsCommands $commandBuilder )
	{
		$this->eventBus       = $eventBus;
		$this->commandBuilder = $commandBuilder;
	}

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		try
		{
			$commandString = $this->readCommandString( $stream );
			$command       = $this->commandBuilder->buildCommand( $commandString );

			$event = $this->createCommandEvent( $command, $stream, $loop );

			$this->eventBus->publishEvent( $event );
		}
		catch ( ClientDisconnectedException $e )
		{
			$stream->shutDown();
			$stream->close();
			$loop->removeStream( $stream );

			$this->eventBus->publishEvent( new ClientDisconnected( $stream ) );
		}
		catch ( UnknownCommandException $e )
		{
			$this->eventBus->publishEvent( new ClientSentUnknownCommand( $stream, $e->getUnknownCommandString() ) );
		}
	}

	private function readCommandString( TransfersData $stream ) : string
	{
		$commandString = $stream->read( ChunkSize::READ );

		if ( !$commandString )
		{
			throw new ClientDisconnectedException( 'Maintenance client disconnected: ' . $stream->getStreamId() );
		}

		return $commandString;
	}

	private function createCommandEvent(
		TriggersExecution $command,
		TransfersData $stream,
		TracksStreams $loop
	) : CarriesEventData
	{
		switch ( $command->getName() )
		{
			case Command::START_MONITOR:
				/** @var StartMonitorCommand $command */
				return new ClientRequestedOverviewMonitor( $stream, $loop, $command );

			case Command::SEARCH_QUEUE:
				/** @var SearchQueueCommand $command */
				return new ClientRequestedQueueSearch( $stream, $command );

			case Command::SHOW_QUEUE:
				/** @var ShowQueueCommand $command */
				return new ClientRequestedQueueMonitor( $stream, $loop, $command );

			case Command::FLUSH_QUEUE:
				/** @var FlushQueueCommand $command */
				return new ClientRequestedFlushingQueue( $stream, $command );

			case Command::FLUSH_ALL_QUEUES:
				/** @var FlushAllQueuesCommand $command */
				return new ClientRequestedFlushingAllQueues( $stream, $command );

			case Command::CLEAR_SCREEN:
				/** @var ClearScreenCommand $command */
				return new ClientRequestedClearScreen( $stream, $command );

			case Command::QUIT_REFRESH:
				/** @var QuitRefreshCommand $command */
				return new ClientRequestedQuittingRefresh( $stream, $loop, $command );

			case Command::QUIT:
				throw new ClientDisconnectedException( 'Client disconnected.' );

			default:
			case Command::HELP:
				/** @var HelpCommand $command */
				return new ClientRequestedHelp( $stream, $command );
		}
	}
}
