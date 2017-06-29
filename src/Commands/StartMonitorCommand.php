<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class StartMonitorCommand
 * @package PHPMQ\Server\Commands
 */
final class StartMonitorCommand implements TriggersExecution
{
	public function getName(): string
	{
		return Command::START_MONITOR;
	}
}
