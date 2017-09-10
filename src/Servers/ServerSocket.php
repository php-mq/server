<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Stream\Interfaces\TransfersData;
use PHPMQ\Stream\Stream;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Servers
 */
final class ServerSocket implements EstablishesStream
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

		$socket = @stream_socket_server(
			$this->socketAddress->getSocketAddress(),
			$errorNumber,
			$errorString,
			STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
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
					'Could not establish server socket at %s: %s [%s].',
					$this->socketAddress->getSocketAddress(),
					$errorString,
					$errorNumber
				)
			);
		}
	}

	public function getStream() : TransfersData
	{
		if ( null === $this->stream )
		{
			$socket = $this->establishSocket();

			$this->stream = new Stream( $socket );
		}

		return $this->stream;
	}
}
