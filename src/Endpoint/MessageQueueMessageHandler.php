<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Endpoint\Events\AcknowledgementWasReceivedEvent;
use PHPMQ\Server\Endpoint\Events\ConsumeRequestWasReceivedEvent;
use PHPMQ\Server\Endpoint\Events\MessageC2EWasReceivedEvent;
use PHPMQ\Server\Endpoint\Exceptions\InvalidMessageTypeReceivedException;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessages;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;

/**
 * Class MessageQueueMessageHandler
 * @package PHPMQ\Server\Endpoint
 */
final class MessageQueueMessageHandler implements HandlesMessages
{
	/** @var PublishesEvents */
	private $eventBus;

	public function __construct( PublishesEvents $eventBus )
	{
		$this->eventBus = $eventBus;
	}

	public function handle( CarriesInformation $message, MessageQueueClient $client ) : void
	{
		$messageType = $message->getMessageType()->getType();

		switch ( $messageType )
		{
			case MessageType::MESSAGE_C2E:
				/** @var MessageC2E $message */
				$event = new MessageC2EWasReceivedEvent( $client, $message );
				break;

			case MessageType::CONSUME_REQUEST:
				/** @var ConsumeRequest $message */
				$event = new ConsumeRequestWasReceivedEvent( $client, $message );
				break;

			case MessageType::ACKNOWLEDGEMENT:
				/** @var Acknowledgement $message */
				$event = new AcknowledgementWasReceivedEvent($client, $message);
				break;

			default:
				throw new InvalidMessageTypeReceivedException( 'Unknown message type: ' . $messageType );
		}

		$this->eventBus->publishEvent( $event );
	}
}
