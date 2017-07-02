<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Endpoint\Interfaces\ListensForActivity;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use PHPMQ\Server\Servers\Interfaces\TracksClients;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AbstractServer
 * @package PHPMQ\Server\Servers
 */
abstract class AbstractServer implements ListensForActivity
{
	use LoggerAwareTrait;

	/** @var EstablishesStream */
	private $socket;

	/** @var TracksClients */
	private $clients;

	public function __construct( EstablishesStream $socket, TracksClients $clients )
	{
		$this->socket  = $socket;
		$this->clients = $clients;
	}

	final protected function getSocket() : EstablishesStream
	{
		return $this->socket;
	}

	final protected function getClients() : TracksClients
	{
		return $this->clients;
	}

	public function start() : void
	{
		$this->getSocket()->startListening();

		$this->logger->debug( 'Start listening on ' . $this->getSocket()->getName() );
	}

	public function stop() : void
	{
		$this->logger->debug( 'Shutting down client connections to ' . $this->getSocket()->getName() );

		$this->getClients()->shutDown();
		$this->getSocket()->endListening();

		$this->logger->debug( 'Stopped listening on ' . $this->getSocket()->getName() );
	}
}
