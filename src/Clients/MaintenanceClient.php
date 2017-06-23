<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Interfaces\BuildsCommands;
use PHPMQ\Server\Clients\Interfaces\TriggersExecution;

/**
 * Class MaintenanceClient
 * @package PHPMQ\Server\Clients
 */
final class MaintenanceClient extends AbstractClient
{
	public function readCommand( BuildsCommands $commandBuilder ) : ?TriggersExecution
	{
		$bytes = $this->read( 1024 );
		$this->guardReadBytes( $bytes );

		return $commandBuilder->buildCommand( $bytes );
	}

	/**
	 * @param bool|null|int $bytes
	 *
	 * @throws \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	private function guardReadBytes( $bytes ) : void
	{
		if ( !$bytes )
		{
			throw new ClientDisconnectedException(
				sprintf( 'MaintenanceClient has disconnected. [MaintenanceClient ID: %s]', $this->getClientId() )
			);
		}
	}
}
