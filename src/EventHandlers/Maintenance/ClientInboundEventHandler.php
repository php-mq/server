<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Maintenance;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Events\Maintenance\ClientRequestedClearScreen;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingAllQueues;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingQueue;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueSearch;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Formatters\ByteFormatter;
use PHPMQ\Server\Monitoring\ServerMonitor;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\StreamListeners\MaintenanceMonitoringListener;
use PHPMQ\Server\Types\QueueName;

/**
 * Class ClientInboundEventHandler
 * @package PHPMQ\Server\EventHandlers\Maintenance
 */
final class ClientInboundEventHandler extends AbstractEventHandler
{
	/** @var StoresMessages */
	private $storage;

	/** @var PreparesOutputForCli */
	private $cliWriter;

	/** @var ServerMonitoringInfo */
	private $serverMonitoringInfo;

	public function __construct(
		StoresMessages $storage,
		PreparesOutputForCli $cliWriter,
		ServerMonitoringInfo $serverMonitoringInfo
	)
	{
		$this->storage              = $storage;
		$this->cliWriter            = $cliWriter;
		$this->serverMonitoringInfo = $serverMonitoringInfo;
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientRequestedHelp::class,
			ClientRequestedOverviewMonitor::class,
			ClientRequestedQueueMonitor::class,
			ClientSentUnknownCommand::class,
			ClientRequestedQuittingRefresh::class,
			ClientRequestedFlushingQueue::class,
			ClientRequestedFlushingAllQueues::class,
			ClientRequestedQueueSearch::class,
			ClientRequestedClearScreen::class,
		];
	}

	protected function whenClientRequestedHelp( ClientRequestedHelp $event ) : void
	{
		$client      = $event->getStream();
		$helpCommand = $event->getHelpCommand();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested help%s.',
				$client->getStreamId(),
				$helpCommand->getCommandName() ? ('for command "' . $helpCommand->getCommandName() . '"') : ''
			)
		);

		$helpFile = $this->getHelpFile( $helpCommand->getCommandName() );

		if ( !file_exists( $helpFile ) )
		{
			$content = $this->cliWriter->clearScreen( 'HELP' )
									   ->writeLn( '' )
									   ->writeLn(
										   'Help for unknown command "%s" requested.',
										   $helpCommand->getCommandName()
									   )
									   ->writeLn( '' )
									   ->writeFileContent( $this->getHelpFile( '' ) )
									   ->getInteractiveOutput();

			$client->write( $content );

			return;
		}

		$this->cliWriter->clearScreen( 'HELP' )->writeFileContent( $helpFile );

		$client->write( $this->cliWriter->getInteractiveOutput() );
	}

	private function getHelpFile( string $forCommand ) : string
	{
		return sprintf(
			'%s/help%s.txt',
			dirname( __DIR__, 3 ) . '/docs/MaintenanceCommandHelp',
			!empty( $forCommand ) ? "-{$forCommand}" : ''
		);
	}

	protected function whenClientRequestedOverviewMonitor( ClientRequestedOverviewMonitor $event ) : void
	{
		$stream = $event->getStream();
		$loop   = $event->getLoop();

		$this->logger->debug( sprintf( 'Maintenance client %s requested monitor.', $stream->getStreamId() ) );

		$listener = new MaintenanceMonitoringListener(
			new QueueName( '' ),
			new ServerMonitor( $this->serverMonitoringInfo, $this->cliWriter )
		);
		$listener->setLogger( $this->logger );

		$loop->addWriteStream( $stream, $listener );
	}

	protected function whenClientRequestedQueueMonitor( ClientRequestedQueueMonitor $event ) : void
	{
		$stream = $event->getStream();
		$loop   = $event->getLoop();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested monitor for queue: %s',
				$stream->getStreamId(),
				$event->getShowQueueCommand()->getQueueName()
			)
		);

		$listener = new MaintenanceMonitoringListener(
			$event->getShowQueueCommand()->getQueueName(),
			new ServerMonitor( $this->serverMonitoringInfo, $this->cliWriter )
		);
		$listener->setLogger( $this->logger );

		$loop->addWriteStream( $stream, $listener );
	}

	protected function whenClientSentUnknownCommand( ClientSentUnknownCommand $event ) : void
	{
		$stream = $event->getStream();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s sent unknown command "%s"',
				$stream->getStreamId(),
				$event->getUnknownCommandString()
			)
		);

		$helpFile = $this->getHelpFile( '' );
		$this->cliWriter->clearScreen( 'HELP' )
						->writeLn( '<bg:red>ERROR:<:bg> Unknown command "%s"', $event->getUnknownCommandString() )
						->writeLn( '' )
						->writeFileContent( $helpFile );

		$stream->write( $this->cliWriter->getInteractiveOutput() );
	}

	protected function whenClientRequestedQuittingRefresh( ClientRequestedQuittingRefresh $event ) : void
	{
		$stream = $event->getStream();
		$loop   = $event->getLoop();

		$this->logger->debug( sprintf( 'Maintenance client %s requested quitting refresh', $stream->getStreamId() ) );

		$loop->removeWriteStream( $stream );

		$stream->write( $this->cliWriter->clearScreen( 'Welcome!' )->getInteractiveOutput() );
	}

	protected function whenClientRequestedFlushingQueue( ClientRequestedFlushingQueue $event ) : void
	{
		$stream            = $event->getStream();
		$flushQueueCommand = $event->getFlushQueueCommand();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested flushing queue "%s"',
				$stream->getStreamId(),
				$flushQueueCommand->getQueueName()
			)
		);

		$this->storage->flushQueue( $flushQueueCommand->getQueueName() );
		$this->serverMonitoringInfo->flushQueue( $flushQueueCommand->getQueueName() );

		$this->logger->debug(
			sprintf(
				'<fg:green>√ Queue %s flushed.<:fg>',
				$flushQueueCommand->getQueueName()
			)
		);

		$stream->write( $this->cliWriter->writeLn( 'OK' )->getInteractiveOutput() );
	}

	protected function whenClientRequestedFlushingAllQueues( ClientRequestedFlushingAllQueues $event ) : void
	{
		$stream = $event->getStream();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested flushing all queues.',
				$stream->getStreamId()
			)
		);

		$this->storage->flushAllQueues();
		$this->serverMonitoringInfo->flushAllQueues();

		$this->logger->debug( '<fg:green>√ All queues flushed.<:fg>' );

		$stream->write( $this->cliWriter->writeLn( 'OK' )->getInteractiveOutput() );
	}

	protected function whenClientRequestedQueueSearch( ClientRequestedQueueSearch $event ) : void
	{
		$stream  = $event->getStream();
		$command = $event->getSearchQueueCommand();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s searched queues for "%s".',
				$stream->getStreamId(),
				$command->getSearchTerm()
			)
		);

		$foundQueueNames = [];
		$searchPattern   = str_replace( '*', '•', $command->getSearchTerm() );
		$searchPattern   = preg_quote( $searchPattern, '#' );
		$searchPattern   = str_replace( '•', '.*', $searchPattern );
		$byteFormatter   = new ByteFormatter();

		foreach ( $this->serverMonitoringInfo->getQueueInfos() as $queueInfo )
		{
			if ( preg_match( "#{$searchPattern}#i", $queueInfo->getQueueName() ) )
			{
				$foundQueueNames[] = sprintf(
					'%s (Messages: %d, Size: %s)',
					$queueInfo->getQueueName(),
					$queueInfo->getMessageCount(),
					$byteFormatter->format( $queueInfo->getSize(), 0 )
				);
			}
		}

		if ( count( $foundQueueNames ) === 0 )
		{
			$this->cliWriter->writeLn( 'No queues found matching: "%s"', $command->getSearchTerm() );
			$stream->write( $this->cliWriter->getInteractiveOutput() );

			return;
		}

		$this->cliWriter->writeLn(
			'Found %d queues matching "%s":',
			(string)count( $foundQueueNames ),
			$command->getSearchTerm()
		);

		foreach ( $foundQueueNames as $foundQueueName )
		{
			$this->cliWriter->writeLn( ' * %s', $foundQueueName );
		}

		$stream->write( $this->cliWriter->getInteractiveOutput() );
	}

	protected function whenClientRequestedClearScreen( ClientRequestedClearScreen $event ) : void
	{
		$stream = $event->getStream();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested to clear screen.',
				$stream->getStreamId()
			)
		);

		$stream->write( $this->cliWriter->clearScreen( 'Welcome!' )->getInteractiveOutput() );
	}
}
