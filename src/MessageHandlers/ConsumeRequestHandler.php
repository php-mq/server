<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\MessageHandlers;

use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Endpoint\Interfaces\HandlesMessage;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use hollodotme\PHPMQ\Protocol\Messages\ConsumeRequest;
use hollodotme\PHPMQ\Protocol\Types\MessageType;

/**
 * Class ConsumeRequestHandler
 * @package hollodotme\PHPMQ\MessageHandlers
 */
final class ConsumeRequestHandler implements HandlesMessage
{
	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool
	{
		return ($messageType->getType() === MessageType::CONSUME_REQUEST);
	}

	/**
	 * @param CarriesInformation|ConsumeRequest $message
	 * @param ConsumesMessages                  $client
	 */
	public function handle( CarriesInformation $message, ConsumesMessages $client ) : void
	{
		$client->updateConsumptionInfo(
			$message->getQueueName(),
			$message->getMessageCount()
		);
	}
}
