<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Streams;

use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\IdentifiesStream;
use PHPMQ\Server\Streams\Exceptions\ReadTimedOutException;
use PHPMQ\Server\Streams\Exceptions\WriteTimedOutException;
use PHPMQ\Server\Timers\TimeoutTimer;

/**
 * Class Stream
 * @package PHPMQ\Server\Streams
 */
final class Stream implements TransfersData
{
	private const READ_WRITE_TIMEOUT_DEFAULT = 500000;

	private const CONNECT_TIMEOUT_DEFAULT    = 2;

	/** @var resource */
	private $stream;

	/** @var IdentifiesStream */
	private $streamId;

	/** @var TimeoutTimer */
	private $timeoutTimer;

	public function __construct(
		$stream,
		int $connectTimeout = self::CONNECT_TIMEOUT_DEFAULT,
		int $readWriteTimeout = self::READ_WRITE_TIMEOUT_DEFAULT
	)
	{
		$this->stream       = $stream;
		$this->streamId     = new StreamId( (string)$stream );
		$this->timeoutTimer = new TimeoutTimer( $readWriteTimeout );
	}

	public function getStreamId() : IdentifiesStream
	{
		return $this->streamId;
	}

	public function read( int $length ) : string
	{
		/** @noinspection UsageOfSilenceOperatorInspection */
		return (string)@fread( $this->stream, $length );
	}

	public function readChunked( int $length, int $chunkSize ) : string
	{
		$buffer = '';
		$this->timeoutTimer->start();

		while ( $length > 0 )
		{
			$bytes = $this->read( (int)min( $length, $chunkSize ) );

			$bytes && $this->timeoutTimer->restart();

			$length -= strlen( $bytes );
			$buffer .= $bytes;

			if ( $this->timeoutTimer->timedOut() )
			{
				throw new ReadTimedOutException( 'Reading from stream in chunks timed out.' );
			}
		}

		$this->timeoutTimer->reset();

		return $buffer;
	}

	public function write( string $content ) : int
	{
		/** @noinspection UsageOfSilenceOperatorInspection */
		return (int)@fwrite( $this->stream, $content );
	}

	public function writeChunked( string $content, int $chunkSize ) : int
	{
		$bytesWritten = 0;
		$bytesToWrite = strlen( $content );

		$this->timeoutTimer->start();

		while ( $bytesToWrite > 0 )
		{
			$written = $this->write( substr( $content, $bytesWritten, (int)min( $bytesToWrite, $chunkSize ) ) );

			$written && $this->timeoutTimer->restart();

			$bytesToWrite -= $written;
			$bytesWritten += $written;

			if ( $this->timeoutTimer->timedOut() )
			{
				throw new WriteTimedOutException( 'Writing to stream in chunks timed out.' );
			}
		}

		$this->timeoutTimer->reset();

		return $bytesWritten;
	}

	public function collectRawStream( array &$rawStreams ) : void
	{
		$rawStreams[ $this->streamId->toString() ] = $this->stream;
	}

	public function acceptConnection() : ?TransfersData
	{
		$connection = @stream_socket_accept( $this->stream, self::CONNECT_TIMEOUT_DEFAULT );

		if ( false === $connection )
		{
			return null;
		}

		if ( !stream_set_blocking( $connection, false ) )
		{
			return null;
		}

		return new Stream( $connection );
	}

	public function hasUnreadBytes() : bool
	{
		/** @noinspection UsageOfSilenceOperatorInspection */
		$metaData = (array)@stream_get_meta_data( $this->stream );

		return ((int)($metaData['unread_bytes'] ?? 0) > 0);
	}

	public function close() : void
	{
		@fclose( $this->stream );
	}

	public function shutDown() : void
	{
		/** @noinspection UsageOfSilenceOperatorInspection */
		@stream_socket_shutdown( $this->stream, STREAM_SHUT_RDWR );
	}
}
