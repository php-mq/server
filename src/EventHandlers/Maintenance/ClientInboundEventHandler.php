<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Maintenance;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingAllQueues;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingQueue;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Types\MonitoringRequest;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
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

	/** @var CollectsServerMonitoringInfo */
	private $serverMonitoringInfo;

	public function __construct(
		StoresMessages $storage,
		PreparesOutputForCli $cliWriter,
		CollectsServerMonitoringInfo $serverMonitoringInfo
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
		];
	}

	protected function whenClientRequestedHelp( ClientRequestedHelp $event ) : void
	{
		$client      = $event->getMaintenanceClient();
		$helpCommand = $event->getHelpCommand();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested help%s.',
				$client->getClientId(),
				$helpCommand->getCommand() ? ('for command "' . $helpCommand->getCommand() . '"') : ''
			)
		);

		$helpFile = $this->getHelpFile( $helpCommand->getCommand() );

		if ( !file_exists( $helpFile ) )
		{
			$content = $this->cliWriter->clearScreen( 'HELP' )
			                           ->writeLn( '' )
			                           ->writeLn(
				                           'Help for unknown command "%s" requested.',
				                           $helpCommand->getCommand()
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
		$client = $event->getMaintenanceClient();

		$this->logger->debug( sprintf( 'Maintenance client %s requested monitor.', $client->getClientId() ) );

		$monitoringRequest = new MonitoringRequest( $client, new QueueName( '' ) );
		$this->serverMonitoringInfo->addMonitoringRequest( $monitoringRequest );
	}

	protected function whenClientRequestedQueueMonitor( ClientRequestedQueueMonitor $event ) : void
	{
		$client = $event->getMaintenanceClient();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested monitor for queue: %s',
				$client->getClientId(),
				$event->getShowQueueCommand()->getQueueName()
			)
		);

		$monitoringRequest = new MonitoringRequest( $client, $event->getShowQueueCommand()->getQueueName() );
		$this->serverMonitoringInfo->addMonitoringRequest( $monitoringRequest );
	}

	protected function whenClientSentUnknownCommand( ClientSentUnknownCommand $event ) : void
	{
		$client = $event->getMaintenanceClient();

		$helpFile = $this->getHelpFile( '' );
		$this->cliWriter->clearScreen( 'HELP' )
		                ->writeLn( '<bg:red>ERROR:<:bg> Unknown command "%s"', $event->getUnknownCommandString() )
		                ->writeLn( '' )
		                ->writeFileContent( $helpFile );

		$client->write( $this->cliWriter->getInteractiveOutput() );
	}

	protected function whenClientRequestedQuittingRefresh( ClientRequestedQuittingRefresh $event ) : void
	{
		$client = $event->getMaintenanceClient();

		$this->logger->debug( sprintf( 'Maintenance client %s requested quitting refresh', $client->getClientId() ) );

		$this->serverMonitoringInfo->removeMonitoringRequest( $client->getClientId() );

		$client->write( $this->cliWriter->clearScreen( 'Welcome!' )->getInteractiveOutput() );
	}

	protected function whenClientRequestedFlushingQueue( ClientRequestedFlushingQueue $event ) : void
	{
		$client            = $event->getMaintenanceClient();
		$flushQueueCommand = $event->getFlushQueueCommand();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested flushing queue "%s"',
				$client->getClientId(),
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

		$client->write( $this->cliWriter->writeLn( 'OK' )->getInteractiveOutput() );
	}

	protected function whenClientRequestedFlushingAllQueues( ClientRequestedFlushingAllQueues $event ) : void
	{
		$client = $event->getMaintenanceClient();

		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested flushing all queues.',
				$client->getClientId()
			)
		);

		$this->storage->flushAllQueues();
		$this->serverMonitoringInfo->flushAllQueues();

		$this->logger->debug( '<fg:green>√ All queues flushed.<:fg>' );

		$client->write( $this->cliWriter->writeLn( 'OK' )->getInteractiveOutput() );
	}
}
