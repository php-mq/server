<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class HelpCommand
 * @package PHPMQ\Server\Commands
 */
final class HelpCommand implements TriggersExecution
{
	/** @var string */
	private $commandName;

	public function __construct( string $commandName )
	{
		$this->commandName = $commandName;
	}

	public function getName() : string
	{
		return Command::HELP;
	}

	public function getCommandName() : string
	{
		return $this->commandName;
	}
}
