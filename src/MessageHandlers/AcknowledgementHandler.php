<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\MessageHandlers;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Endpoint\Interfaces\HandlesMessage;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use hollodotme\PHPMQ\Protocol\Messages\Acknowledgement;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AcknowledgementHandler
 * @package hollodotme\PHPMQ\MessageHandlers
 */
final class AcknowledgementHandler implements HandlesMessage
{
	use LoggerAwareTrait;

	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool
	{
		return ($messageType->getType() === MessageType::ACKNOWLEDGEMENT);
	}

	/**
	 * @param CarriesInformation|Acknowledgement $message
	 * @param ConsumesMessages|Client            $client
	 */
	public function handle( CarriesInformation $message, ConsumesMessages $client ) : void
	{
		$this->logger->debug( '' );
		$this->logger->debug(
			sprintf(
				'Received %s for queue "%s" with content:',
				get_class( $message ),
				$message->getQueueName()->toString()
			)
		);

		$this->logger->debug( $message->toString() );

		$this->storage->dequeue( $message->getQueueName(), $message->getMessageId() );

		$this->logger->debug( '√ Message dequeued' );

		$client->acknowledgeMessage( $message->getQueueName(), $message->getMessageId() );

		$this->logger->debug(
			sprintf(
				'√ Updated consumption info of client: %s to queue "%s" and count "%s".',
				$client->getClientId()->toString(),
				$client->getConsumptionQueueName()->toString(),
				$client->getConsumptionMessageCount()
			)
		);

		$this->logger->debug( '' );
	}
}
