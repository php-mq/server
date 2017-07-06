<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class ClearScreenCommand
 * @package PHPMQ\Server\Commands
 */
final class ClearScreenCommand implements TriggersExecution
{
	public function getName() : string
	{
		return Command::CLEAR_SCREEN;
	}
}
