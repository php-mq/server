<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Builders;

use PHPMQ\Protocol\Constants\PacketType;
use PHPMQ\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Protocol\Interfaces\DefinesMessage;
use PHPMQ\Protocol\Interfaces\ProvidesMessageData;
use PHPMQ\Protocol\Messages\Acknowledgement;
use PHPMQ\Protocol\Messages\ConsumeRequest;
use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Protocol\Messages\MessageServerToClient;
use PHPMQ\Protocol\Types\MessageType;
use PHPMQ\Server\Builders\Exceptions\MessageTypeNotImplementedException;
use PHPMQ\Server\Builders\Exceptions\PacketCountMismatchException;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;

/**
 * Class MessageBuilder
 * @package PHPMQ\Server\Protocol\Messages
 */
final class MessageBuilder implements BuildsMessages
{
	public function buildMessage( DefinesMessage $messageHeader, array $packets ) : ProvidesMessageData
	{
		$this->guardPacketCountMatchesMessageType( $messageHeader, $packets );

		switch ( $messageHeader->getMessageType()->getType() )
		{
			case MessageType::MESSAGE_CLIENT_TO_SERVER:
			{
				return new MessageClientToServer(
					new QueueName( (string)$packets[ PacketType::QUEUE_NAME ] ),
					(string)$packets[ PacketType::MESSAGE_CONTENT ]
				);
				break;
			}

			case MessageType::CONSUME_REQUEST:
			{
				return new ConsumeRequest(
					new QueueName( (string)$packets[ PacketType::QUEUE_NAME ] ),
					(int)$packets[ PacketType::MESSAGE_CONSUME_COUNT ]
				);
				break;
			}

			case MessageType::MESSAGE_SERVER_TO_CLIENT:
			{
				return new MessageServerToClient(
					new MessageId( (string)$packets[ PacketType::MESSAGE_ID ] ),
					new QueueName( (string)$packets[ PacketType::QUEUE_NAME ] ),
					(string)$packets[ PacketType::MESSAGE_CONTENT ]
				);
				break;
			}

			case MessageType::ACKNOWLEDGEMENT:
			{
				return new Acknowledgement(
					new QueueName( (string)$packets[ PacketType::QUEUE_NAME ] ),
					new MessageId( (string)$packets[ PacketType::MESSAGE_ID ] )
				);
				break;
			}

			default:
			{
				throw new MessageTypeNotImplementedException(
					'Message type not implemented: '
					. $messageHeader->getMessageType()->getType()
				);
			}
		}
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
