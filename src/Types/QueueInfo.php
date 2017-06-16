<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Types;

/**
 * Class QueueInfo
 * @package PHPMQ\Server\Types
 */
final class QueueInfo
{
	/** @var string */
	private $queueName;

	/** @var array */
	private $messageInfos;

	public function __construct( string $queueName, array $messageInfos )
	{
		$this->queueName    = $queueName;
		$this->messageInfos = $messageInfos;
	}

	public function getQueueName(): string
	{
		return $this->queueName;
	}

	public function getSize(): int
	{
		return (int)array_sum( array_column( $this->messageInfos, 'size' ) );
	}

	public function getMessageInfos(): array
	{
		return $this->messageInfos;
	}

	public function getMessageCount(): int
	{
		return count( $this->messageInfos );
	}
}
