<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\BuildsCommands;
use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Commands\Exceptions\UnknownCommandException;
use PHPMQ\Server\Types\QueueName;

/**
 * Class CommandBuilder
 * @package PHPMQ\Server\Commands
 */
final class CommandBuilder implements BuildsCommands
{
	public function buildCommand( string $commandString ) : TriggersExecution
	{
		$parts     = array_filter( explode( ' ', trim( $commandString ) ) );
		$command   = $parts[0];
		$arguments = array_slice( $parts, 1 );

		switch ( $command )
		{
			case Command::HELP:
				return new Help( (string)($arguments[0] ?? '') );
				break;

			case Command::START_MONITOR:
				return new StartMonitor();
				break;

			case Command::SHOW_QUEUE:
				return new ShowQueue( new QueueName( (string)($arguments[0] ?? '') ) );
				break;

			case Command::QUIT:
			case Command::EXIT:
				return new Quit();
				break;

			default:
				throw (new UnknownCommandException())->withUnknownCommandString( $commandString );
		}
	}
}
