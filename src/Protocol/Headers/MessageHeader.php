<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Protocol\Headers;

use PHPMQ\Server\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Server\Protocol\Types\MessageType;

/**
 * Class MessageHeader
 * @package PHPMQ\Server\Protocol\Headers
 */
final class MessageHeader extends AbstractHeader
{
	private const PACKET_ID = 'H';

	/** @var int */
	private $version;

	/** @var IdentifiesMessageType */
	private $messageType;

	public function __construct( int $version, IdentifiesMessageType $messageType )
	{
		parent::__construct( self::PACKET_ID );

		$this->version     = $version;
		$this->messageType = $messageType;
	}

	public function getVersion() : int
	{
		return $this->version;
	}

	public function getMessageType() : IdentifiesMessageType
	{
		return $this->messageType;
	}

	public function toString() : string
	{
		return sprintf(
			'%s%02d%03d%02d',
			$this->getIdentifier(),
			$this->version,
			$this->messageType->getType(),
			$this->messageType->getPacketCount()
		);
	}

	public static function fromString( string $string ) : self
	{
		return new self(
			(int)substr( $string, 1, 2 ),
			new MessageType( (int)substr( $string, 3, 3 ) )
		);
	}
}
