<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run\Clients;

use PHPMQ\Protocol\Constants\PacketType;
use PHPMQ\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Protocol\Interfaces\DefinesMessage;
use PHPMQ\Protocol\Interfaces\ProvidesMessageData;
use PHPMQ\Protocol\Messages\MessageServerToClient;
use PHPMQ\Protocol\Types\MessageType;
use PHPMQ\Server\Builders\Exceptions\MessageTypeNotImplementedException;
use PHPMQ\Server\Builders\Exceptions\PacketCountMismatchException;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;

/**
 * Class MessageBuilder
 * @package PHPMQ\Server\Tests\Run\Clients
 */
final class MessageBuilder implements BuildsMessages
{
	public function buildMessage( DefinesMessage $messageHeader, array $packets ) : ProvidesMessageData
	{
		$this->guardPacketCountMatchesMessageType( $messageHeader, $packets );

		$messageType = $messageHeader->getMessageType()->getType();

		if ( $messageType === MessageType::MESSAGE_SERVER_TO_CLIENT )
		{
			return new MessageServerToClient(
				new MessageId( (string)$packets[ PacketType::MESSAGE_ID ] ),
				new QueueName( (string)$packets[ PacketType::QUEUE_NAME ] ),
				(string)$packets[ PacketType::MESSAGE_CONTENT ]
			);
		}

		throw new MessageTypeNotImplementedException(
			'Message type not implemented: '
			. $messageHeader->getMessageType()->getType()
		);
	}

	private function guardPacketCountMatchesMessageType( DefinesMessage $messageHeader, array $packets ) : void
	{
		if ( $messageHeader->getMessageType()->getPacketCount() !== count( $packets ) )
		{
			throw new PacketCountMismatchException(
				'Packet count does not match expectation of message type.'
			);
		}
	}
}
