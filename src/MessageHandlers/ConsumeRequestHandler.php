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
use hollodotme\PHPMQ\Protocol\Messages\ConsumeRequest;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ConsumeRequestHandler
 * @package hollodotme\PHPMQ\MessageHandlers
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
