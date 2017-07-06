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
		$parts    = array_filter( explode( ' ', trim( $commandString ), 2 ) );
		$command  = $parts[0];
		$argument = trim( (string)($parts[1] ?? ''), '\'"' );

		switch ( $command )
		{
			case Command::HELP:
				return new HelpCommand( $argument );
				break;

			case Command::START_MONITOR:
				return new StartMonitorCommand();
				break;

			case Command::SEARCH_QUEUE:
				return new SearchQueueCommand( $argument );
				break;

			case Command::SHOW_QUEUE:
				return new ShowQueueCommand( new QueueName( $argument ) );
				break;

			case Command::FLUSH_QUEUE:
				return new FlushQueueCommand( new QueueName( $argument ) );
				break;

			case Command::FLUSH_ALL_QUEUES:
				return new FlushAllQueuesCommand();
				break;

			case Command::QUIT_REFRESH:
				return new QuitRefreshCommand();
				break;

			case Command::QUIT:
			case Command::EXIT:
				return new QuitCommand();
				break;

			default:
				throw (new UnknownCommandException())->withUnknownCommandString( $commandString );
		}
	}
}
