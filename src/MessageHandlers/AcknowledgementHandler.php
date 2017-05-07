<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\MessageHandlers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessage;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AcknowledgementHandler
 * @package PHPMQ\Server\MessageHandlers
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

		$consumptionInfo = $client->getConsumptionInfo();

		if ( $consumptionInfo->getQueueName()->equals( $message->getQueueName() ) )
		{
			$consumptionInfo->removeMessageId( $message->getMessageId() );
		}

		$this->logger->debug(
			sprintf(
				'√ Updated consumption info of client: %s to %s',
				$client->getClientId()->toString(),
				$consumptionInfo->toString()
			)
		);

		$this->logger->debug( '' );
	}
}
