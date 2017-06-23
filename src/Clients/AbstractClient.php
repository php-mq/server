<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Servers\Interfaces\CommunicatesWithServer;

/**
 * Class AbstractClient
 * @package PHPMQ\Server\Clients
 */
abstract class AbstractClient implements CommunicatesWithServer
{
	/** @var IdentifiesClient */
	private $clientId;

	/** @var resource */
	private $socket;

	public function __construct( IdentifiesClient $clientId, $socket )
	{
		$this->clientId = $clientId;
		$this->socket   = $socket;
	}

	public function getClientId(): IdentifiesClient
	{
		return $this->clientId;
	}

	public function read( int $bytes ): string
	{
		return (string)fread( $this->socket, $bytes );
	}

	public function collectSocket( array &$sockets ): void
	{
		$sockets[ $this->clientId->toString() ] = $this->socket;
	}

	public function hasUnreadData(): bool
	{
		$metaData = stream_get_meta_data( $this->socket );

		return ($metaData['unread_bytes'] > 0);
	}

	public function write( string $data ): int
	{
		/** @noinspection UsageOfSilenceOperatorInspection */
		return (int)(@fwrite( $this->socket, $data ));
	}

	public function shutDown(): void
	{
		stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
	}
}
