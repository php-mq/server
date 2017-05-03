<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Messages;

use hollodotme\PHPMQ\Exceptions\LogicException;
use hollodotme\PHPMQ\Exceptions\RuntimeException;
use hollodotme\PHPMQ\Protocol\Constants\PacketType;
use hollodotme\PHPMQ\Protocol\Interfaces\BuildsMessages;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\MessageHeader;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Types\MessageId;
use hollodotme\PHPMQ\Types\QueueName;

/**
 * Class MessageBuilder
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class MessageBuilder implements BuildsMessages
{
	public function buildMessage( MessageHeader $messageHeader, array $packets ) : CarriesInformation
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
				throw new RuntimeException(
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
			throw new LogicException( 'Packet count does not match expectation of message type.' );
		}
	}
}
