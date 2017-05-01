<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol\Messages;

use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
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
 * Class Acknowledgement
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class Acknowledgement implements CarriesInformation
{
	use StringRepresenting;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var IdentifiesMessage */
	private $messageId;

	/** @var IdentifiesMessageType */
	private $messageType;

	public function __construct( IdentifiesQueue $queueName, IdentifiesMessage $messageId )
	{
		$this->queueName   = $queueName;
		$this->messageId   = $messageId;
		$this->messageType = new MessageType( MessageType::ACKNOWLEDGEMENT );
	}

	public function getMessageType() : IdentifiesMessageType
	{
		return $this->messageType;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}

	public function getMessageId() : IdentifiesMessage
	{
		return $this->messageId;
	}

	public function toString() : string
	{
		$messageHeader         = new MessageHeader( ProtocolVersion::VERSION_1, $this->messageType );
		$queuePacketHeader     = new PacketHeader( PacketType::QUEUE_NAME, strlen( (string)$this->queueName ) );
		$messageIdPacketHeader = new PacketHeader( PacketType::MESSAGE_ID, strlen( (string)$this->messageId ) );

		return $messageHeader
		       . $queuePacketHeader
		       . $this->queueName
		       . $messageIdPacketHeader
		       . $this->messageId;
	}
}
