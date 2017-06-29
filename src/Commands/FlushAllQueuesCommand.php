<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class FlushAllQueuesCommand
 * @package PHPMQ\Server\Commands
 */
final class FlushAllQueuesCommand implements TriggersExecution
{
	public function getName() : string
	{
		return Command::FLUSH_ALL_QUEUES;
	}
}
