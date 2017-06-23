<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Commands;

use PHPMQ\Server\Clients\Interfaces\TriggersExecution;
use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Commands\Exceptions\InvalidCommandException;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Class ShowQueue
 * @package PHPMQ\Server\Commands
 */
final class ShowQueue implements TriggersExecution
{
	/** @var IdentifiesQueue */
	private $queueName;

	public function __construct( IdentifiesQueue $queueName )
	{
		$this->guardQueueNameIsValid( $queueName );

		$this->queueName = $queueName;
	}

	private function guardQueueNameIsValid( IdentifiesQueue $queueName ): void
	{
		if ( empty( $queueName->toString() ) )
		{
			throw new InvalidCommandException( 'A queue name must be provided.' );
		}
	}

	public function getName(): string
	{
		return Command::SHOW_QUEUE;
	}

	public function getQueueName(): IdentifiesQueue
	{
		return $this->queueName;
	}
}
