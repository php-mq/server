<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Maintenance;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Types\MonitoringRequest;
use PHPMQ\Server\Types\QueueName;

/**
 * Class ClientInboundEventHandler
 * @package PHPMQ\Server\EventHandlers\Maintenance
 */
final class ClientInboundEventHandler extends AbstractEventHandler
{
	/** @var PreparesOutputForCli */
	private $cliWriter;

	/** @var CollectsServerMonitoringInfo */
	private $serverMonitoringInfo;

	public function __construct( PreparesOutputForCli $cliWriter, CollectsServerMonitoringInfo $serverMonitoringInfo )
	{
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
		$this->cliWriter->clearScreen( 'HELP' )->writeFileContent( $helpFile );

		$client->write( $this->cliWriter->getOutput() );
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

		$client->write( $this->cliWriter->getOutput() );
	}

	protected function whenClientRequestedQuittingRefresh( ClientRequestedQuittingRefresh $event ) : void
	{
		$client = $event->getMaintenanceClient();

		$this->logger->debug( sprintf( 'Maintenance client %s requested quitting refresh', $client->getClientId() ) );

		$this->serverMonitoringInfo->removeMonitoringRequest( $client->getClientId() );

		$client->write( $this->cliWriter->clearScreen( 'Welcome!' )->getOutput() );
	}
}
