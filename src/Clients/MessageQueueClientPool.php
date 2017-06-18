<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Exceptions\ReadFailedException;
use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Endpoint\EventBus;
use PHPMQ\Server\Endpoint\Events\ClientHasConnectedEvent;
use PHPMQ\Server\Endpoint\Events\ClientHasDisconnectedEvent;
use PHPMQ\Server\Endpoint\MessageQueueMessageHandler;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageQueueClientPool
 * @package PHPMQ\Server\Clients
 */
final class MessageQueueClientPool implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/** @var array|MessageQueueClient[] */
	private $clients;

	/** @var MessageBuilder */
	private $messageBuilder;

	/** @var MessageQueueMessageHandler */
	private $messageHandler;

	/** @var EventBus */
	private $eventBus;

	public function __construct( MessageBuilder $messageBuilder, MessageQueueMessageHandler $messageHandler, EventBus $eventBus )
	{
		$this->clients        = [];
		$this->messageBuilder = $messageBuilder;
		$this->messageHandler = $messageHandler;
		$this->eventBus       = $eventBus;
	}

	public function add( IdentifiesClient $clientId, $socket ) : void
	{
		$client = new MessageQueueClient( $clientId, $socket, $this->messageBuilder );

		$this->clients[ $clientId->toString() ] = $client;

		$this->eventBus->publishEvent( new ClientHasConnectedEvent( $client ) );
	}

	public function removeAllClients() : void
	{
		foreach ( $this->clients as $client )
		{
			$this->remove( $client->getClientId() );
		}
	}

	public function remove( IdentifiesClient $clientId ) : void
	{
		$client = $this->clients[ $clientId->toString() ] ?? null;

		if ( null === $client )
		{
			return;
		}

		$client->shutDown();

		unset( $this->clients[ $clientId->toString() ] );

		$this->eventBus->publishEvent( new ClientHasDisconnectedEvent( $client ) );
	}

	public function handleMessagesFromClients() : void
	{
		foreach ( $this->getActive() as $client )
		{
			$messages = $this->readMessagesFromClient( $client );

			$this->handleMessages( $client, $messages );
		}
	}

	/**
	 * @return array|MessageQueueClient[]
	 */
	private function getActive() : array
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

		if ( !@stream_select( $reads, $writes, $exepts, 0 ) )
		{
			return [];
		}

		return array_intersect_key( $this->clients, $reads );
	}

	private function readMessagesFromClient( MessageQueueClient $client ) : \Generator
	{
		do
		{
			$message = $this->readMessageFromClient( $client );

			if ( null === $message )
			{
				break;
			}

			yield $message;
		}
		while ( $client->hasUnreadData() );
	}

	private function readMessageFromClient( MessageQueueClient $client ) : ?CarriesInformation
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
			$this->remove( $client->getClientId() );

			return null;
		}
	}

	private function handleMessages( MessageQueueClient $client, iterable $messages ) : void
	{
		/** @var CarriesInformation $message */
		foreach ( $messages as $message )
		{
			$this->messageHandler->handle( $message, $client );
		}
	}
}
