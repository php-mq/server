<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Class FlushQueueCommand
 * @package PHPMQ\Server\Commands
 */
final class FlushQueueCommand implements TriggersExecution
{
	/** @var IdentifiesQueue */
	private $queueName;

	public function __construct( IdentifiesQueue $queueName )
	{
		$this->queueName = $queueName;
	}

	public function getName() : string
	{
		return Command::FLUSH_QUEUE;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}
}
