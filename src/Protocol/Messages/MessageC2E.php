<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Messages;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Protocol\Constants\PacketType;
use PHPMQ\Server\Protocol\Constants\ProtocolVersion;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class MessageC2E
 * @package PHPMQ\Server\Protocol\Messages
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
