<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Interfaces\BuildsCommands;
use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Commands\CommandBuilder;
use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Commands\ShowQueue;
use PHPMQ\Server\Commands\StartMonitor;
use PHPMQ\Server\Events\Maintenance\ClientConnected;
use PHPMQ\Server\Events\Maintenance\ClientDisconnected;
use PHPMQ\Server\Events\Maintenance\ClientRequestedMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Servers\Interfaces\EstablishesActivityListener;

/**
 * Class MaintenanceServer
 * @package PHPMQ\Server\Servers
 */
final class MaintenanceServer extends AbstractServer
{
	/** @var BuildsCommands */
	private $commandBuilder;

	public function __construct( EstablishesActivityListener $socket )
	{
		parent::__construct( $socket );
		$this->commandBuilder = new CommandBuilder();
	}

	public function getEvents(): \Generator
	{
		$newClientInfo = $this->getSocket()->getNewClient();

		if ( null !== $newClientInfo )
		{
			$clientId = new ClientId( $newClientInfo->getName() );
			$client   = new MaintenanceClient( $clientId, $newClientInfo->getSocket() );

			$this->getClients()->add( $client );

			yield new ClientConnected( $client );
		}

		yield from $this->createInboundMessageEvents();
	}

	private function createInboundMessageEvents(): \Generator
	{
		/** @var MaintenanceClient $client */
		foreach ( $this->getClients()->getActive() as $client )
		{
			try
			{
				$commands = $this->readCommandsFromClient( $client );

				yield from $this->createEventsForCommands( $commands, $client );
			}
			catch ( ClientDisconnectedException $e )
			{
				$this->getClients()->remove( $client );

				yield new ClientDisconnected( $client );
			}
		}
	}

	/**
	 * @param MaintenanceClient $client
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @return \Generator|TriggersExecution[]
	 */
	private function readCommandsFromClient( MaintenanceClient $client ): \Generator
	{
		do
		{
			$command = $client->readCommand( $this->commandBuilder );

			if ( null === $command )
			{
				break;
			}

			yield $command;
		}
		while ( $client->hasUnreadData() );
	}

	/**
	 * @param iterable          $commands
	 * @param MaintenanceClient $client
	 *
	 * @return \Generator|CarriesEventData[]
	 */
	private function createEventsForCommands( iterable $commands, MaintenanceClient $client ): \Generator
	{
		/** @var TriggersExecution $command */
		foreach ( $commands as $command )
		{
			yield $this->createCommandEvent( $command, $client );
		}
	}

	private function createCommandEvent( TriggersExecution $command, MaintenanceClient $client ): CarriesEventData
	{
		switch ( $command->getName() )
		{
			case Command::START_MONITOR:
				/** @var StartMonitor $command */
				return new ClientRequestedMonitor( $client, $command );

			case Command::SHOW_QUEUE:
				/** @var ShowQueue $command */
				return new ClientRequestedQueueMonitor( $client, $command );

			default:
				throw new RuntimeException( 'Unknown command name: ' . $command->getName() );
		}
	}
}
