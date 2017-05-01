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
 * Class MessageC2E
 * @package hollodotme\PHPMQ\Protocol\Messages
 */
final class MessageC2E implements CarriesInformation
{
	use StringRepresenting;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var string */
	private $content;

	/** @var IdentifiesMessageType */
	private $messageType;

	public function __construct( IdentifiesQueue $queueName, string $content )
	{
		$this->queueName   = $queueName;
		$this->content     = $content;
		$this->messageType = new MessageType( MessageType::MESSAGE_C2E );
	}

	public function getMessageType() : IdentifiesMessageType
	{
		return $this->messageType;
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
		$messageHeader       = new MessageHeader( ProtocolVersion::VERSION_1, $this->messageType );
		$queuePacketHeader   = new PacketHeader( PacketType::QUEUE_NAME, strlen( (string)$this->queueName ) );
		$contentPacketHeader = new PacketHeader( PacketType::MESSAGE_CONTENT, strlen( $this->content ) );

		return $messageHeader
		       . $queuePacketHeader
		       . $this->queueName
		       . $contentPacketHeader
		       . $this->content;
	}
}
