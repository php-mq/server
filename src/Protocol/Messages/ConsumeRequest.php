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
 * Class ConsumeRequest
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class ConsumeRequest implements RepresentsString
{
	use StringRepresenting;

	/** @var string */
	private $queueName;

	/** @var int */
	private $messageCount;

	public function __construct( string $queueName, int $messageCount )
	{
		$this->queueName    = $queueName;
		$this->messageCount = $messageCount;
	}

	public function getQueueName() : string
	{
		return $this->queueName;
	}

	public function getMessageCount() : int
	{
		return $this->messageCount;
	}

	public function toString() : string
	{
		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::CONSUME_REQUEST )
		);

		$queuePacketHeader        = new PacketHeader( PacketType::QUEUE_NAME, strlen( $this->queueName ) );
		$messageCountPacketHeader = new PacketHeader(
			PacketType::MESSAGE_CONSUME_COUNT,
			strlen( (string)$this->messageCount )
		);

		return $messageHeader->toString()
		       . $queuePacketHeader->toString()
		       . $this->queueName
		       . $messageCountPacketHeader->toString()
		       . $this->messageCount;
	}
}
