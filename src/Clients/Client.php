<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Clients;

use hollodotme\PHPMQ\Clients\Interfaces\IdentifiesClient;
use hollodotme\PHPMQ\Exceptions\RuntimeException;

/**
 * Class Client
 * @package hollodotme\PHPMQ\Clients
 */
final class Client
{
	/** @var resource */
	private $socket;

	/** @var IdentifiesClient */
	private $clientId;

	/** @var string */
	private $buffer;

	/** @var bool */
	private $isDisconnected;

	public function __construct( IdentifiesClient $clientId, $socket )
	{
		$this->clientId       = $clientId;
		$this->socket         = $socket;
		$this->buffer         = '';
		$this->isDisconnected = false;

		socket_set_nonblock( $this->socket );
	}

	public function getClientId() : IdentifiesClient
	{
		return $this->clientId;
	}

	public function collectSocket( array &$sockets ) : void
	{
		$sockets[ $this->clientId->toString() ] = $this->socket;
	}

	public function read() : string
	{
		$bytes = socket_recv( $this->socket, $this->buffer, 2048, MSG_DONTWAIT );

		if ( false !== $bytes )
		{
			if ( null === $this->buffer )
			{
				$this->isDisconnected = true;

				return '';
			}

			return $this->buffer;
		}

		throw new RuntimeException(
			'socket_recv() failed; reason: '
			. socket_strerror( socket_last_error( $this->socket ) )
		);
	}

	public function isDisconnected() : bool
	{
		return $this->isDisconnected;
	}
}
