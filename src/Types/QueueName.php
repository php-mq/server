<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Types;

use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;
use hollodotme\PHPMQ\Traits\StringRepresenting;

/**
 * Class QueueName
 * @package hollodotme\PHPMQ\Types
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
