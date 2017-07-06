<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Trait QueueIdentifierMocking
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait QueueIdentifierMocking
{
	protected function getQueueName( string $queueName ) : IdentifiesQueue
	{
		return new class($queueName) implements IdentifiesQueue
		{
			use StringRepresenting;

			/** @var string */
			private $queueName;

			public function __construct( string $queueName )
			{
				$this->queueName = $queueName;
			}

			public function equals( IdentifiesQueue $other ) : bool
			{
				return ($other->toString() === $this->toString());
			}

			public function toString() : string
			{
				return $this->queueName;
			}
		};
	}
}
