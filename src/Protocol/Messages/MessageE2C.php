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
 * Class MessageE2C
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class MessageE2C implements CarriesInformation
{
	use StringRepresenting;

	/** @var IdentifiesMessage */
	private $messageId;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var string */
	private $content;

	/** @var IdentifiesMessageType */
	private $messageType;

	public function __construct( IdentifiesMessage $messageId, IdentifiesQueue $queueName, string $content )
	{
		$this->messageId   = $messageId;
		$this->queueName   = $queueName;
		$this->content     = $content;
		$this->messageType = new MessageType( MessageType::MESSAGE_E2C );
	}

	public function getMessageType() : IdentifiesMessageType
	{
		return $this->messageType;
	}

	public function getMessageId() : IdentifiesMessage
	{
		return $this->messageId;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}

	public function getContent() : string
	{
		return $this->content;
	}

	public function toString() : string
	{
		$messageHeader         = new MessageHeader( ProtocolVersion::VERSION_1, $this->messageType );
		$queuePacketHeader     = new PacketHeader( PacketType::QUEUE_NAME, strlen( $this->queueName->toString() ) );
		$contentPacketHeader   = new PacketHeader( PacketType::MESSAGE_CONTENT, strlen( $this->content ) );
		$messageIdPacketHeader = new PacketHeader( PacketType::MESSAGE_ID, strlen( $this->messageId->toString() ) );

		return $messageHeader
		       . $queuePacketHeader
		       . $this->queueName
		       . $contentPacketHeader
		       . $this->content
		       . $messageIdPacketHeader
		       . $this->messageId;
	}
}
