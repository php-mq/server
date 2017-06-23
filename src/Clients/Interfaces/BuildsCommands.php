<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Clients\Interfaces;

/**
 * Interface BuildsCommands
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface BuildsCommands
{
	public function buildCommand( string $commandString ): TriggersExecution;
}
