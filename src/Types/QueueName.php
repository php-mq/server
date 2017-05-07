<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Types;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Traits\StringRepresenting;

/**
 * Class QueueName
 * @package PHPMQ\Server\Types
 */
final class QueueName implements IdentifiesQueue
{
	use StringRepresenting;

	/** @var string */
	private $queueName;

	public function __construct( string $queueName )
	{
		$this->queueName = $queueName;
	}

	public function toString() : string
	{
		return $this->queueName;
	}
}
