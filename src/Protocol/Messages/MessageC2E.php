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
 * Class MessageC2E
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class MessageC2E implements RepresentsString
{
	use StringRepresenting;

	/** @var string */
	private $queueName;

	/** @var string */
	private $content;

	public function __construct( string $queueName, string $content )
	{
		$this->queueName = $queueName;
		$this->content   = $content;
	}

	public function getQueueName() : string
	{
		return $this->queueName;
	}

	public function getContent() : string
	{
		return $this->content;
	}

	public function toString() : string
	{
		$messageHeader = new MessageHeader(
			ProtocolVersion::VERSION_1,
			new MessageType( MessageType::MESSAGE_C2E )
		);

		$queuePacketHeader   = new PacketHeader( PacketType::QUEUE_NAME, strlen( $this->queueName ) );
		$contentPacketHeader = new PacketHeader( PacketType::MESSAGE_CONTENT, strlen( $this->content ) );

		return $messageHeader->toString()
		       . $queuePacketHeader->toString()
		       . $this->queueName
		       . $contentPacketHeader->toString()
		       . $this->content;
	}
}
