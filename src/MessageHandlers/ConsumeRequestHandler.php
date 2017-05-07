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
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Types\MessageType;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ConsumeRequestHandler
 * @package PHPMQ\Server\MessageHandlers
 */
final class ConsumeRequestHandler implements HandlesMessage
{
	use LoggerAwareTrait;

	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool
	{
		return ($messageType->getType() === MessageType::CONSUME_REQUEST);
	}

	/**
	 * @param CarriesInformation|ConsumeRequest $message
	 * @param ConsumesMessages|Client           $client
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

		$client->updateConsumptionInfo(
			$message->getQueueName(),
			$message->getMessageCount()
		);

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
