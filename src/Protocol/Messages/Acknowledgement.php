<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Messages;

use hollodotme\PHPMQ\Interfaces\RepresentsString;
use hollodotme\PHPMQ\Protocol\Constants\PacketType;
use hollodotme\PHPMQ\Protocol\Constants\ProtocolVersion;
use hollodotme\PHPMQ\Protocol\MessageHeader;
use hollodotme\PHPMQ\Protocol\PacketHeader;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Traits\StringRepresenting;

/**
 * Class Acknowledgement
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class Acknowledgement implements RepresentsString
{
	use StringRepresenting;

	/** @var string */
	private $queueName;

	/** @var string */
	private $messageId;

	public function __construct( string $queueName, string $messageId )
	{
		$this->queueName = $queueName;
		$this->messageId = $messageId;
	}

	public function getQueueName() : string
	{
		return $this->queueName;
	}

	public function getMessageId() : string
	{
		return $this->messageId;
	}

	public function toString() : string
	{
		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::ACKNOWLEDGEMENT )
		);

		$queuePacketHeader     = new PacketHeader( PacketType::QUEUE_NAME, strlen( $this->queueName ) );
		$messageIdPacketHeader = new PacketHeader( PacketType::MESSAGE_ID, strlen( $this->messageId ) );

		return $messageHeader->toString()
		       . $queuePacketHeader->toString()
		       . $this->queueName
		       . $messageIdPacketHeader->toString()
		       . $this->messageId;
	}
}
