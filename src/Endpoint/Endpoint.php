<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Exceptions\ReadFailedException;
use PHPMQ\Server\Clients\Interfaces\CollectsClients;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Endpoint\Interfaces\ConfiguresEndpoint;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessages;
use PHPMQ\Server\Endpoint\Interfaces\ListensToClients;
use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Loggers\Monitoring\ServerMonitor;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class Endpoint
 * @package PHPMQ\Server\Endpoint
 */
final class Endpoint implements ListensToClients, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/** @var ConfiguresEndpoint */
	private $config;

	/** @var CollectsClients */
	private $clients;

	/** @var HandlesMessages */
	private $messageHandler;

	/** @var ServerMonitor */
	private $monitor;

	/** @var resource */
	private $socket;

	/** @var bool */
	private $listening;

	/** @var BuildsMessages */
	private $messageBuilder;

	public function __construct(
		ConfiguresEndpoint $config,
		CollectsClients $clientCollection,
		HandlesMessages $messageHandler,
		ServerMonitor $monitor
	)
	{
		$this->config         = $config;
		$this->clients        = $clientCollection;
		$this->messageHandler = $messageHandler;
		$this->monitor        = $monitor;
		$this->listening      = false;
		$this->messageBuilder = new MessageBuilder();
	}

	public function startListening(): void
	{
		$this->registerSignalHandler();
		$this->establishSocket();

		$this->listening = true;

		$this->logger->debug( 'Start listening for client connections...' );

		$this->loop();
	}

	private function registerSignalHandler(): void
	{
		if ( function_exists( 'pcntl_signal' ) )
		{
			pcntl_signal( SIGTERM, [ $this, 'shutDownBySignal' ] );
			pcntl_signal( SIGINT, [ $this, 'shutDownBySignal' ] );
		}
	}

	private function shutDownBySignal( int $signal ): void
	{
		if ( in_array( $signal, [ SIGINT, SIGTERM, SIGKILL ], true ) )
		{
			$this->endListening();
			exit( 0 );
		}
	}

	public function endListening(): void
	{
		$this->listening = false;

		if ( null !== $this->socket )
		{
			$this->logger->debug( 'Shutting down.' );

			$this->clients->shutDown();
			fclose( $this->socket );
			$this->socket = null;
		}
	}

	private function establishSocket(): void
	{
		if ( null !== $this->socket )
		{
			return;
		}

		$errorNumber = null;
		$errorString = null;

		$this->socket = @stream_socket_server( $this->config->getSocketAddress(), $errorNumber, $errorString );

		if ( $this->socket === false )
		{
			throw new RuntimeException( 'Could not establish socket: ' . $errorString );
		}

		stream_set_blocking( $this->socket, false );
	}

	private function loop(): void
	{
		declare(ticks=1);

		while ( $this->listening )
		{
			usleep( 2000 );

			$this->checkForNewClient();

			$this->readMessagesFromActiveClients();

			$this->clients->dispatchMessages();

			$this->monitor->refresh();
		}
	}

	private function checkForNewClient(): void
	{
		$read  = [ $this->socket ];
		$write = $except = null;

		if ( stream_select( $read, $write, $except, 0 ) )
		{
			$clientSocket = stream_socket_accept( $this->socket, 0 );

			if ( $clientSocket !== false )
			{
				stream_set_blocking( $clientSocket, false );

				$clientId = ClientId::generate();
				$client   = new Client( $clientId, $clientSocket, $this->messageBuilder );

				$this->clients->add( $client );
			}
		}
	}

	private function readMessagesFromActiveClients(): void
	{
		foreach ( $this->clients->getActive() as $client )
		{
			$message = $this->readMessageFromClient( $client );

			if ( null !== $message )
			{
				$this->messageHandler->handle( $message, $client );
			}
		}
	}

	private function readMessageFromClient( Client $client ): ?CarriesInformation
	{
		try
		{
			return $client->readMessage();
		}
		catch ( ReadFailedException $e )
		{
			$this->logger->alert( $e->getMessage() );

			return null;
		}
		catch ( ClientDisconnectedException $e )
		{
			$this->clients->remove( $client );

			return null;
		}
	}

	public function __destruct()
	{
		$this->endListening();
	}
}
