<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Clients\ClientPool;
use PHPMQ\Server\Endpoint\Interfaces\ListensForActivity;
use PHPMQ\Server\Servers\Interfaces\EstablishesActivityListener;
use PHPMQ\Server\Servers\Interfaces\TracksClients;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AbstractServer
 * @package PHPMQ\Server\Servers
 */
abstract class AbstractServer implements ListensForActivity
{
	use LoggerAwareTrait;

	/** @var EstablishesActivityListener */
	private $socket;

	/** @var TracksClients */
	private $clients;

	public function __construct( EstablishesActivityListener $socket )
	{
		$this->socket  = $socket;
		$this->clients = new ClientPool();
	}

	final protected function getSocket() : EstablishesActivityListener
	{
		return $this->socket;
	}

	final protected function getClients() : TracksClients
	{
		return $this->clients;
	}

	public function start() : void
	{
		$this->socket->startListening();

		$this->logger->debug( 'Start listening on ' . $this->socket->getName() );
	}

	public function stop() : void
	{
		$this->logger->debug( 'Shutting down client connections to ' . $this->socket->getName() );

		$this->clients->shutDown();
		$this->socket->endListening();

		$this->logger->debug( 'Stopped listening on ' . $this->socket->getName() );
	}
}
