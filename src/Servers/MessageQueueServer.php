<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Events\MessageQueue\ClientGotReadyForConsumingMessages;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessageC2E;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Servers\Interfaces\EstablishesActivityListener;

/**
 * Class MessageQueueServer
 * @package PHPMQ\Server\Servers
 */
final class MessageQueueServer extends AbstractServer
{
	/** @var MessageBuilder */
	private $messageBuilder;

	public function __construct( EstablishesActivityListener $socket )
	{
		parent::__construct( $socket );
		$this->messageBuilder = new MessageBuilder();
	}

	/**
	 * @return \Generator|CarriesEventData[]
	 */
	public function getEvents() : \Generator
	{
		$newClientInfo = $this->getSocket()->getNewClient();

		if ( null !== $newClientInfo )
		{
			$clientId = new ClientId( $newClientInfo->getName() );
			$client   = new MessageQueueClient( $clientId, $newClientInfo->getSocket() );

			$this->getClients()->add( $client );

			yield new ClientConnected( $client );
		}

		yield from $this->createInboundMessageEvents();

		yield from $this->createOutboundMessageEvents();
	}

	private function createInboundMessageEvents() : \Generator
	{
		/** @var MessageQueueClient $client */
		foreach ( $this->getClients()->getActive() as $client )
		{
			try
			{
				$messages = $this->readMessagesFromClient( $client );

				yield from $this->createEventsForMessages( $messages, $client );
			}
			catch ( ClientDisconnectedException $e )
			{
				$this->getClients()->remove( $client );

				yield new ClientDisconnected( $client );
			}
		}
	}

	/**
	 * @param MessageQueueClient $client
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 * @return \Generator|CarriesInformation[]
	 */
	private function readMessagesFromClient( MessageQueueClient $client ) : \Generator
	{
		do
		{
			$message = $client->readMessage( $this->messageBuilder );

			if ( null === $message )
			{
				break;
			}

			yield $message;
		}
		while ( $client->hasUnreadData() );
	}

	/**
	 * @param iterable           $messages
	 * @param MessageQueueClient $client
	 *
	 * @return \Generator|CarriesEventData[]
	 */
	private function createEventsForMessages( iterable $messages, MessageQueueClient $client ) : \Generator
	{
		/** @var CarriesInformation $message */
		foreach ( $messages as $message )
		{
			yield $this->createMessageEvent( $message, $client );
		}
	}

	private function createMessageEvent( CarriesInformation $message, MessageQueueClient $client ) : CarriesEventData
	{
		$messageType = $message->getMessageType()->getType();

		switch ( $messageType )
		{
			case MessageType::MESSAGE_C2E:
				/** @var MessageC2E $message */
				return new ClientSentMessageC2E( $client, $message );

			case MessageType::CONSUME_REQUEST:
				/** @var ConsumeRequest $message */
				return new ClientSentConsumeResquest( $client, $message );

			case MessageType::ACKNOWLEDGEMENT:
				/** @var Acknowledgement $message */
				return new ClientSentAcknowledgement( $client, $message );

			default:
				throw new InvalidMessageTypeReceivedException( 'Unknown message type: ' . $messageType );
		}
	}

	/**
	 * @return \Generator|CarriesEventData[]
	 */
	private function createOutboundMessageEvents() : \Generator
	{
		/** @var MessageQueueClient $client */
		foreach ( $this->getClients()->getShuffled() as $client )
		{
			$consumptionInfo = $client->getConsumptionInfo();

			if ( $consumptionInfo->canConsume() )
			{
				yield new ClientGotReadyForConsumingMessages( $client );
			}
		}
	}
}
