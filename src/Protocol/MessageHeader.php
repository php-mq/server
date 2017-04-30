<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Protocol;

use hollodotme\PHPMQ\Protocol\Types\MessageType;

/**
 * Class MessageHeader
 * @package hollodotme\PHPMQ\Protocol
 */
final class MessageHeader extends AbstractPacket
{
	private const PACKET_ID = 'H';

	/** @var int */
	private $version;

	/** @var int */
	private $messageType;

	/** @var int */
	private $packageCount;

	public function __construct( int $version, MessageType $messageType )
	{
		parent::__construct( self::PACKET_ID );

		$this->version      = $version;
		$this->messageType  = $messageType->getType();
		$this->packageCount = $messageType->getPackageCount();
	}

	public function getVersion() : int
	{
		return $this->version;
	}

	public function getMessageType() : int
	{
		return $this->messageType;
	}

	public function getPackageCount() : int
	{
		return $this->packageCount;
	}

	public function toString() : string
	{
		return sprintf(
			'%s%02d%03d%02d',
			$this->getIdentifier(),
			$this->version,
			$this->messageType,
			$this->packageCount
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
