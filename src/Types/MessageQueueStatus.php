<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Types;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Storage\Interfaces\ProvidesQueueStatus;

/**
 * Class MessageQueueStatus
 * @package PHPMQ\Server\Types
 */
final class MessageQueueStatus implements ProvidesQueueStatus
{
	/** @var array */
	private $statusData;

	public function __construct( array $statusData )
	{
		$this->statusData = $statusData;
	}

	public function getQueueName(): IdentifiesQueue
	{
		return new QueueName( (string)$this->statusData['queueName'] );
	}

	public function getCountTotal(): int
	{
		return (int)($this->statusData['countTotal'] ?? 0);
	}

	public function getCountUndispatched(): int
	{
		return (int)($this->statusData['countUndispatched'] ?? 0);
	}

	public function getCountDispatched(): int
	{
		return (int)($this->statusData['countDispatched'] ?? 0);
	}
}
