<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Clients\ClientPool;
use PHPMQ\Server\Endpoint\Interfaces\ListensForActivity;
use PHPMQ\Server\Servers\Interfaces\EstablishesActivityListener;

/**
 * Class AdminServer
 * @package PHPMQ\Server\Servers
 */
final class AdminServer implements ListensForActivity
{
	/** @var EstablishesActivityListener */
	private $socket;

	/** @var ClientPool */
	private $clients;

	public function __construct( EstablishesActivityListener $socket )
	{
		$this->socket  = $socket;
		$this->clients = new ClientPool();
	}

	public function start() : void
	{
		$this->socket->startListening();
	}

	public function stop() : void
	{
		$this->clients->shutDown();
		$this->socket->endListening();
	}

	public function getEvents() : \Generator
	{
		yield null;
	}
}
