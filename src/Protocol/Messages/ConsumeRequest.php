<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Messages;

use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;
use hollodotme\PHPMQ\Protocol\Constants\PacketType;
use hollodotme\PHPMQ\Protocol\Constants\ProtocolVersion;
use hollodotme\PHPMQ\Protocol\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use hollodotme\PHPMQ\Protocol\MessageHeader;
use hollodotme\PHPMQ\Protocol\PacketHeader;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Traits\StringRepresenting;

/**
 * Class ConsumeRequest
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class ConsumeRequest implements CarriesInformation
{
	use StringRepresenting;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var int */
	private $messageCount;

	/** @var IdentifiesMessageType */
	private $messageType;

	public function __construct( IdentifiesQueue $queueName, int $messageCount )
	{
		$this->queueName    = $queueName;
		$this->messageCount = $messageCount;
		$this->messageType  = new MessageType( MessageType::CONSUME_REQUEST );
	}

	public function getMessageType() : IdentifiesMessageType
	{
		return $this->messageType;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}

	public function getMessageCount() : int
	{
		return $this->messageCount;
	}

	public function toString() : string
	{
		$messageHeader            = new MessageHeader( ProtocolVersion::VERSION_1, $this->messageType );
		$queuePacketHeader        = new PacketHeader( PacketType::QUEUE_NAME, strlen( (string)$this->queueName ) );
		$messageCountPacketHeader = new PacketHeader(
			PacketType::MESSAGE_CONSUME_COUNT,
			strlen( (string)$this->messageCount )
		);

		return $messageHeader
		       . $queuePacketHeader
		       . $this->queueName
		       . $messageCountPacketHeader
		       . $this->messageCount;
	}
}
