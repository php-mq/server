<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Trait MessageIdentifierMocking
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait MessageIdentifierMocking
{
	protected function getMessageId( string $messageId ) : IdentifiesMessage
	{
		return new class($messageId) implements IdentifiesMessage
		{
			use StringRepresenting;

			/** @var string */
			private $messageId;

			public function __construct( string $messageId )
			{
				$this->messageId = $messageId;
			}

			public static function generate() : IdentifiesMessage
			{
				return new static( bin2hex( random_bytes( 16 ) ) );
			}

			public function toString() : string
			{
				return $this->messageId;
			}
		};
	}
}
