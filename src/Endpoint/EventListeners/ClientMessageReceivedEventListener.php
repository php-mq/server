<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\EventListeners;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Endpoint\Events\AcknowledgementWasReceivedEvent;
use PHPMQ\Server\Endpoint\Events\ConsumeRequestWasReceivedEvent;
use PHPMQ\Server\Endpoint\Events\MessageC2EWasReceivedEvent;
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
			MessageC2EWasReceivedEvent::class,
			ConsumeRequestWasReceivedEvent::class,
			AcknowledgementWasReceivedEvent::class,
		];
	}

	protected function whenMessageC2EWasReceived( MessageC2EWasReceivedEvent $event ) : void
	{
		$messageC2E   = $event->getMessageC2E();
		$storeMessage = new Message( MessageId::generate(), $messageC2E->getContent() );

		$this->storage->enqueue( $messageC2E->getQueueName(), $storeMessage );
	}

	protected function whenConsumeRequestWasReceived( ConsumeRequestWasReceivedEvent $event ) : void
	{
		$client         = $event->getClient();
		$consumeRequest = $event->getConsumeRequest();

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

	protected function whenAcknowledgementWasReceived( AcknowledgementWasReceivedEvent $event ) : void
	{
		$client          = $event->getClient();
		$acknowledgement = $event->getAcknowledgement();

		$this->storage->dequeue( $acknowledgement->getQueueName(), $acknowledgement->getMessageId() );

		$consumptionInfo = $client->getConsumptionInfo();

		if ( $consumptionInfo->getQueueName()->equals( $acknowledgement->getQueueName() ) )
		{
			$consumptionInfo->removeMessageId( $acknowledgement->getMessageId() );
		}
	}
}
