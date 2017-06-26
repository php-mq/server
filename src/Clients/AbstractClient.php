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
	private const CHUNK_SIZE = 1024;

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
		$buffer    = '';
		$chunkSize = (int)min( $bytes, self::CHUNK_SIZE );

		while ( $bytes > 0 )
		{
			$buffer    .= (string)fread( $this->socket, $chunkSize );
			$bytes     -= self::CHUNK_SIZE;
			$chunkSize = (int)min( $bytes, self::CHUNK_SIZE );
		}

		return $buffer;
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
		for ( $written = 0, $writtenMax = strlen( $data ); $written < $writtenMax; $written += $bytes )
		{
			$bytes = @fwrite( $this->socket, substr( $data, $written ) );
			if ( $bytes === false )
			{
				return $written;
			}
		}

		return $written;
	}

	public function shutDown(): void
	{
		stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
		fclose( $this->socket );
	}
}
