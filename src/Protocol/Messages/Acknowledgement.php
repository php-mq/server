<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Messages;

use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Protocol\Constants\PacketType;
use PHPMQ\Server\Protocol\Constants\ProtocolVersion;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class Acknowledgement
 * @package PHPMQ\Server\Protocol\Messages
 */
final class Acknowledgement implements CarriesMessageData
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
