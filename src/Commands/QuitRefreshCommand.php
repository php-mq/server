<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class QuitRefreshCommand
 * @package PHPMQ\Server\Commands
 */
final class QuitRefreshCommand implements TriggersExecution
{
	public function getName() : string
	{
		return Command::QUIT_REFRESH;
	}
}
