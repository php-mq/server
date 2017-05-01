<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\MessageHandlers;

use hollodotme\PHPMQ\Endpoint\Interfaces\HandlesMessage;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use hollodotme\PHPMQ\Protocol\Messages\MessageC2E;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;
use hollodotme\PHPMQ\Types\Message;
use hollodotme\PHPMQ\Types\MessageId;

/**
 * Class MessageC2EHandler
 * @package hollodotme\PHPMQ\MessageHandlers
 */
final class MessageC2EHandler implements HandlesMessage
{
	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	public function acceptsMessageType( IdentifiesMessageType $messageType ) : bool
	{
		return ($messageType->getType() === MessageType::MESSAGE_C2E);
	}

	/**
	 * @param CarriesInformation|MessageC2E $message
	 */
	public function handle( CarriesInformation $message ) : void
	{
		$storeMessage = new Message( MessageId::generate(), $message->getContent() );

		$this->storage->enqueue( $message->getQueueName(), $storeMessage );
	}
}
