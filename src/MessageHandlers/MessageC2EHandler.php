<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\MessageHandlers;

use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessage;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageC2EHandler
 * @package PHPMQ\Server\MessageHandlers
 */
final class MessageC2EHandler implements HandlesMessage
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
		return ($messageType->getType() === MessageType::MESSAGE_C2E);
	}

	/**
	 * @param CarriesInformation|MessageC2E $message
	 * @param ConsumesMessages              $client
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

		$storeMessage = new Message( MessageId::generate(), $message->getContent() );

		$this->storage->enqueue( $message->getQueueName(), $storeMessage );

		$this->logger->debug( '√ Stored message with ID: ' . $storeMessage->getMessageId()->toString() );
	}
}
