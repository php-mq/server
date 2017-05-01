<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Interfaces\IdentifiesClient;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use hollodotme\PHPMQ\Endpoint\Constants\SocketShutdownMode;
use hollodotme\PHPMQ\Endpoint\Interfaces\AcceptsMessageHandlers;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConfiguresEndpoint;
use hollodotme\PHPMQ\Endpoint\Interfaces\HandlesMessage;
use hollodotme\PHPMQ\Endpoint\Interfaces\ListensToClients;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class Endpoint
 * @package hollodotme\PHPMQ\Endpoint
 */
final class Endpoint implements ListensToClients, AcceptsMessageHandlers, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/** @var ConfiguresEndpoint */
	private $config;

	/** @var resource */
	private $socket;

	/** @var array|Client[] */
	private $clients;

	/** @var bool */
	private $listening;

	/** @var array|HandlesMessage[] */
	private $messageHandlers;

	public function __construct( ConfiguresEndpoint $config )
	{
		$this->config          = $config;
		$this->clients         = [];
		$this->listening       = false;
		$this->messageHandlers = [];
	}

	public function addMessageHandlers( HandlesMessage ...$messageHandlers ) : void
	{
		foreach ( $messageHandlers as $messageHandler )
		{
			$this->messageHandlers[] = $messageHandler;
		}
	}

	public function startListening() : void
	{
		$this->establishSocket();

		$this->listening = true;

		while ( $this->listening )
		{
			$this->checkForNewClient();

			foreach ( $this->getActiveClients() as $client )
			{
				if ( $client->isDisconnected() )
				{
					$this->logger->debug( 'Client disconnected: ' . $client->getClientId() );

					$this->removeClient( $client->getClientId() );

					$this->logger->debug( 'Memory: ' . (memory_get_peak_usage( true ) / 1024 / 1024) . ' MB' );

					continue;
				}

				$this->logger->debug( $client->read() );
			}
		}
	}

	private function establishSocket() : void
	{
		if ( null !== $this->socket )
		{
			return;
		}

		$this->socket = socket_create(
			$this->config->getSocketDomain(),
			$this->config->getSocketType(),
			$this->config->getSocketProtocol()
		);

		if ( file_exists( $this->config->getBindToAddress()->getAddress() ) )
		{
			@unlink( $this->config->getBindToAddress()->getAddress() );
		}

		socket_bind(
			$this->socket,
			$this->config->getBindToAddress()->getAddress(),
			$this->config->getBindToAddress()->getPort()
		);

		socket_listen( $this->socket, $this->config->getListenBacklog() );
		socket_set_nonblock( $this->socket );
	}

	private function checkForNewClient() : void
	{
		$clientSocket = socket_accept( $this->socket );

		if ( $clientSocket !== false )
		{
			socket_set_nonblock( $clientSocket );

			$clientId = ClientId::generate();
			$client   = new Client( $clientId, $clientSocket );

			$this->logger->debug( 'New client connected: ' . $clientId );

			$this->clients[ $clientId->toString() ] = $client;
		}
	}

	/**
	 * @return array|Client[]
	 */
	private function getActiveClients() : array
	{
		if ( empty( $this->clients ) )
		{
			return [];
		}

		$reads  = [];
		$writes = $exepts = null;

		foreach ( $this->clients as $client )
		{
			$client->collectSocket( $reads );
		}

		socket_select( $reads, $writes, $exepts, 0 );

		return array_intersect_key( $this->clients, $reads );
	}

	private function removeClient( IdentifiesClient $clientId ) : void
	{
		unset( $this->clients[ $clientId->toString() ] );
	}

	public function endListening() : void
	{
		$this->listening = false;

		if ( null !== $this->socket )
		{
			socket_shutdown( $this->socket, SocketShutdownMode::READING_WRITING );
			socket_close( $this->socket );
			$this->socket = null;
		}
	}

	public function __destruct()
	{
		$this->endListening();
	}
}
