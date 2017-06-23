<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;

/**
 * Class Help
 * @package PHPMQ\Server\Commands
 */
final class Help implements TriggersExecution
{
	/** @var string */
	private $command;

	public function __construct( string $command )
	{
		$this->command = $command;
	}

	public function getName() : string
	{
		return Command::HELP;
	}

	public function getCommand() : string
	{
		return $this->command;
	}
}
