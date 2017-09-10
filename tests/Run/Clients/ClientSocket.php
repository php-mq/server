<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run\Clients;

use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Stream\Interfaces\TransfersData;
use PHPMQ\Stream\Stream;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Tests\Run\Clients
 */
final class ClientSocket implements EstablishesStream
{
	/** @var resource */
	private $stream;

	/** @var string */
	private $socketAddress;

	public function __construct( IdentifiesSocketAddress $socketAddress )
	{
		$this->socketAddress = $socketAddress;
	}

	/**
	 * @return resource
	 */
	private function establishSocket()
	{
		$errorNumber = $errorString = null;
		$context     = stream_context_create( $this->socketAddress->getContextOptions() );

		$socket = @stream_socket_client(
			$this->socketAddress->getSocketAddress(),
			$errorNumber,
			$errorString,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		$this->guardSocketEstablished( $socket, $errorNumber, $errorString );

		return $socket;
	}

	private function guardSocketEstablished( $socket, ?int $errorNumber, ?string $errorString ) : void
	{
		if ( false === $socket )
		{
			throw new RuntimeException(
				sprintf(
					'Could not establish client socket at %s: %s [%s].',
					$this->socketAddress->getSocketAddress(),
					$errorString,
					$errorNumber
				)
			);
		}
	}

	private function makeSocketNonBlocking( $socket ) : void
	{
		if ( !stream_set_blocking( $socket, false ) )
		{
			throw new RuntimeException(
				sprintf(
					'Could not set client socket at %s to non-blocking.',
					$this->socketAddress->getSocketAddress()
				)
			);
		}
	}

	public function getStream() : TransfersData
	{
		if ( null === $this->stream )
		{
			$socket = $this->establishSocket();
			$this->makeSocketNonBlocking( $socket );

			$this->stream = new Stream( $socket );
		}

		return $this->stream;
	}
}
