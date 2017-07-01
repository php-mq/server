<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Messages;

use PHPMQ\Server\Protocol\Constants\PacketType;
use PHPMQ\Server\Protocol\Exceptions\MessageTypeNotImplementedException;
use PHPMQ\Server\Protocol\Exceptions\PacketCountMismatchException;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;

/**
 * Class MessageBuilder
 * @package PHPMQ\Server\Protocol\Messages
 */
final class MessageBuilder implements BuildsMessages
{
	public function buildMessage( MessageHeader $messageHeader, array $packets ) : CarriesMessageData
	{
		$this->guardPacketCountMatchesMessageType( $messageHeader, $packets );

		switch ( $messageHeader->getMessageType()->getType() )
		{
			case MessageType::MESSAGE_C2E:
			{
				return new MessageC2E(
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

			case MessageType::MESSAGE_E2C:
			{
				return new MessageE2C(
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

	private function guardPacketCountMatchesMessageType( MessageHeader $messageHeader, array $packets ) : void
	{
		if ( $messageHeader->getMessageType()->getPacketCount() !== count( $packets ) )
		{
			throw new PacketCountMismatchException(
				'Packet count does not match expectation of message type.'
			);
		}
	}
}
