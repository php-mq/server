<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Traits\StringRepresenting;
use PHPMQ\Stream\Interfaces\IdentifiesStream;

/**
 * Trait StreamIdentifierMocking
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait StreamIdentifierMocking
{
	protected function getStreamId( string $streamId ) : IdentifiesStream
	{
		return new class($streamId) implements IdentifiesStream
		{
			use StringRepresenting;

			/** @var string */
			private $streamId;

			public function __construct( string $streamId )
			{
				$this->streamId = $streamId;
			}

			public function equals( IdentifiesStream $other ) : bool
			{
				return ($other->toString() === $this->toString());
			}

			public function toString() : string
			{
				return $this->streamId;
			}
		};
	}
}
