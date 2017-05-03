<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\MessageHandlers;

use hollodotme\PHPMQ\Endpoint\Interfaces\ConsumesMessages;
use hollodotme\PHPMQ\Endpoint\Interfaces\HandlesMessage;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use hollodotme\PHPMQ\Protocol\Messages\Acknowledgement;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;

/**
 * Class AcknowledgementHandler
 * @package hollodotme\PHPMQ\MessageHandlers
 */
final class AcknowledgementHandler implements HandlesMessage
{
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
	 * @param ConsumesMessages                   $client
	 */
	public function handle( CarriesInformation $message, ConsumesMessages $client ) : void
	{
		$this->storage->dequeue( $message->getQueueName(), $message->getMessageId() );

		$client->acknowledgeMessage( $message->getQueueName(), $message->getMessageId() );
	}
}
