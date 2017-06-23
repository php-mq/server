<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Maintenance;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;

/**
 * Class ClientInboundEventHandler
 * @package PHPMQ\Server\EventHandlers\Maintenance
 */
final class ClientInboundEventHandler extends AbstractEventHandler
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
			ClientRequestedHelp::class,
			ClientRequestedMonitor::class,
			ClientRequestedQueueMonitor::class,
			ClientSentUnknownCommand::class,
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
		$this->cliWriter->clearScreen()->writeFileContent( $helpFile );

		$client->write( $this->cliWriter->get() );
	}

	private function getHelpFile( string $forCommand ) : string
	{
		return sprintf(
			'%s/help%s.txt',
			dirname( __DIR__, 3 ) . '/docs/MaintenanceCommandHelp',
			!empty( $forCommand ) ? "-{$forCommand}" : ''
		);
	}

	protected function whenClientRequestedMonitor( ClientRequestedMonitor $event ) : void
	{
		$client = $event->getMaintenanceClient();
		$this->logger->debug( sprintf( 'Maintenance client %s requested monitor.', $client->getClientId() ) );
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
	}

	protected function whenClientSentUnknownCommand( ClientSentUnknownCommand $event ) : void
	{
		$client = $event->getMaintenanceClient();

		$helpFile = $this->getHelpFile( '' );
		$this->cliWriter->clearScreen()
		                ->writeLn( '<bg:red>ERROR:<:bg> Unknown command "%s"', $event->getUnknownCommandString() )
		                ->writeLn( '' )
		                ->writeFileContent( $helpFile );

		$client->write( $this->cliWriter->get() );
	}
}
