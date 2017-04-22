<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Types;

use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Traits\StringRepresenting;

/**
 * Class MessageId
 * @package hollodotme\PHPMQ\Types
 */
final class MessageId implements IdentifiesMessage
{
	use StringRepresenting;

	/** @var string */
	private $messageId;

	public function __construct( string $messageId )
	{
		$this->messageId = $messageId;
	}

	public function toString() : string
	{
		return $this->messageId;
	}

	public static function generate() : IdentifiesMessage
	{
		return new self( bin2hex( random_bytes( 16 ) ) );
	}
}
