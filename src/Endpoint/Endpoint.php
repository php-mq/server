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
use PHPMQ\Server\Endpoint\Constants\SocketShutdownMode;
use PHPMQ\Server\Endpoint\Interfaces\AcceptsMessageHandlers;
use PHPMQ\Server\Endpoint\Interfaces\ConfiguresEndpoint;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessage;
use PHPMQ\Server\Endpoint\Interfaces\ListensToClients;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class Endpoint
 * @package PHPMQ\Server\Endpoint
 */
final class Endpoint implements ListensToClients, AcceptsMessageHandlers, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/** @var ConfiguresEndpoint */
	private $config;

	/** @var resource */
	private $socket;

	/** @var CollectsClients */
	private $clients;

	/** @var bool */
	private $listening;

	/** @var BuildsMessages */
	private $messageBuilder;

	/** @var array|HandlesMessage[] */
	private $messageHandlers;

	public function __construct( ConfiguresEndpoint $config, CollectsClients $clientCollection )
	{
		$this->config          = $config;
		$this->clients         = $clientCollection;
		$this->listening       = false;
		$this->messageBuilder  = new MessageBuilder();
		$this->messageHandlers = [];
	}

	public function addMessageHandlers( HandlesMessage ...$messageHandlers ) : void
	{
		foreach ( $messageHandlers as $messageHandler )
		{
			$messageHandler->setLogger( $this->logger );

			$this->messageHandlers[] = $messageHandler;
		}
	}

	public function startListening() : void
	{
		$this->establishSocket();

		$this->listening = true;

		$this->logger->debug( 'Start listening for client connections...' );

		while ( $this->listening )
		{
			$this->checkForNewClient();

			foreach ( $this->clients->getActive() as $client )
			{
				$message = $this->readMessageFromClient( $client );

				if ( null !== $message )
				{
					$this->handleMessageFromClient( $client, $message );
				}
			}

			$this->clients->dispatchMessages();
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
			$client   = new Client( $clientId, $clientSocket, $this->messageBuilder );

			$this->clients->add( $client );
		}
	}

	private function readMessageFromClient( Client $client ) : ?CarriesInformation
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

	private function handleMessageFromClient( ConsumesMessages $client, CarriesInformation $message ) : void
	{
		foreach ( $this->messageHandlers as $messageHandler )
		{
			if ( $messageHandler->acceptsMessageType( $message->getMessageType() ) )
			{
				$messageHandler->handle( $message, $client );
			}
		}
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
