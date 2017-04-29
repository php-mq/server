<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint;

use hollodotme\PHPMQ\Endpoint\Constants\SocketShutdownMode;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConfiguresEndpoint;
use hollodotme\PHPMQ\Endpoint\Interfaces\ListensToClients;
use hollodotme\PHPMQ\Endpoint\Interfaces\ReceivesMessages;

/**
 * Class Endpoint
 * @package hollodotme\PHPMQ\Endpoint
 */
final class Endpoint implements ReceivesMessages, ListensToClients
{
	private const STREAM_SELECT_USEC = 20000;

	/** @var ConfiguresEndpoint */
	private $config;

	/** @var resource */
	private $socket;

	public function __construct( ConfiguresEndpoint $config )
	{
		$this->config = $config;
	}

	public function startListening() : void
	{
		if ( null !== $this->socket )
		{
			return;
		}

		$this->socket = socket_create(
			$this->config->getSocketDomain(),
			$this->config->getSocketType(),
			$this->config->getSocketProtocol()
		);

		socket_bind(
			$this->socket,
			$this->config->getBindToAddress()->getAddress(),
			$this->config->getBindToAddress()->getPort()
		);

		socket_listen( $this->socket, $this->config->getListenBacklog() );
	}

	public function endListening() : void
	{
		if ( null !== $this->socket )
		{
			socket_shutdown( $this->socket, SocketShutdownMode::READING_WRITING );
			socket_close( $this->socket );
		}
	}

	public function hasMessages() : bool
	{
		$writes = [ $this->socket ];
		$reads  = $exepts = null;

		return (bool)stream_select( $reads, $writes, $exepts, 0, self::STREAM_SELECT_USEC );
	}

	public function readMessages() : \Generator
	{
		// TODO: Implement readMessages() method.
	}

	public function __destruct()
	{
		$this->endListening();
	}
}
