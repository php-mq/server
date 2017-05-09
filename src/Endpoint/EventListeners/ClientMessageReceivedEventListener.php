<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\EventListeners;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Endpoint\Events\ClientMessageWasReceivedEvent;
use PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;

/**
 * Class ClientMessageReceivedEventListener
 * @package PHPMQ\Server\Endpoint\EventListeners
 */
final class ClientMessageReceivedEventListener extends AbstractEventListener
{
	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientMessageWasReceivedEvent::class,
		];
	}

	/**
	 * @param ClientMessageWasReceivedEvent $event
	 *
	 * @throws \PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException
	 */
	protected function whenClientMessageWasReceived( ClientMessageWasReceivedEvent $event ) : void
	{
		$client  = $event->getClient();
		$message = $event->getMessage();

		$messageType = $message->getMessageType()->getType();

		switch ( $messageType )
		{
			case MessageType::MESSAGE_C2E:
				/** @var MessageC2E $message */
				$this->handleMessageC2E( $message );
				break;

			case MessageType::CONSUME_REQUEST:
				/** @var ConsumeRequest $message */
				$this->handleConsumeRequest( $message, $client );
				break;

			case MessageType::ACKNOWLEDGEMENT:
				/** @var Acknowledgement $message */
				$this->handleAcknowledgement( $message, $client );
				break;

			default:
				throw new InvalidMessageTypeReceivedException(
					'Unknown message type: ' . $messageType
				);
		}
	}

	private function handleMessageC2E( MessageC2E $messageC2E ) : void
	{
		$storeMessage = new Message( MessageId::generate(), $messageC2E->getContent() );

		$this->storage->enqueue( $messageC2E->getQueueName(), $storeMessage );
	}

	private function handleConsumeRequest( ConsumeRequest $consumeRequest, Client $client ) : void
	{
		$this->cleanUpClientConsumption( $client );

		$client->updateConsumptionInfo(
			new ConsumptionInfo(
				$consumeRequest->getQueueName(),
				$consumeRequest->getMessageCount()
			)
		);
	}

	private function cleanUpClientConsumption( Client $client ) : void
	{
		$consumptionInfo = $client->getConsumptionInfo();
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );

			$consumptionInfo->removeMessageId( $messageId );
		}
	}

	private function handleAcknowledgement( Acknowledgement $acknowledgement, Client $client ) : void
	{
		$this->storage->dequeue( $acknowledgement->getQueueName(), $acknowledgement->getMessageId() );

		$consumptionInfo = $client->getConsumptionInfo();

		if ( $consumptionInfo->getQueueName()->equals( $acknowledgement->getQueueName() ) )
		{
			$consumptionInfo->removeMessageId( $acknowledgement->getMessageId() );
		}
	}
}
