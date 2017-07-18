<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Exceptions\RuntimeException;

/**
 * Class Loop
 * @package PHPMQ\Server\Endpoint
 */
final class Loop implements TracksStreams
{
	private const STREAM_SELECT_TIMEOUT_USEC = 200000;

	/** @var array|TransfersData[] */
	private $readStreams = [];

	/** @var array|ListensForStreamActivity[] */
	private $readStreamListeners = [];

	/** @var array|TransfersData[] */
	private $writeStreams = [];

	/** @var array|ListensForStreamActivity[] */
	private $writeStreamListeners = [];

	/** @var bool */
	private $isRunning = false;

	public function addReadStream( TransfersData $stream, ListensForStreamActivity $listener ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		$this->readStreams[ $streamId ]         = $stream;
		$this->readStreamListeners[ $streamId ] = $listener;
	}

	public function addWriteStream( TransfersData $stream, ListensForStreamActivity $listener ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		$this->writeStreams[ $streamId ]         = $stream;
		$this->writeStreamListeners[ $streamId ] = $listener;
	}

	public function removeAllStreams() : void
	{
		foreach ( $this->readStreams as $stream )
		{
			$this->removeReadStream( $stream );
			$stream->shutDown();
			$stream->close();
		}

		foreach ( $this->writeStreams as $stream )
		{
			$this->removeWriteStream( $stream );
			$stream->shutDown();
			$stream->close();
		}
	}

	public function removeStream( TransfersData $stream ) : void
	{
		$this->removeReadStream( $stream );
		$this->removeWriteStream( $stream );
	}

	public function removeReadStream( TransfersData $stream ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		unset( $this->readStreams[ $streamId ], $this->readStreamListeners[ $streamId ] );
	}

	public function removeWriteStream( TransfersData $stream ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		unset( $this->writeStreams[ $streamId ], $this->writeStreamListeners[ $streamId ] );
	}

	public function start() : void
	{
		$this->registerSignalHandler();

		$this->isRunning = (count( $this->readStreams ) + count( $this->writeStreams ) > 0);

		declare(ticks=1);

		while ( $this->isRunning )
		{
			$this->waitForStreamActivity();
		}
	}

	private function registerSignalHandler() : void
	{
		if ( function_exists( 'pcntl_signal' ) )
		{
			pcntl_signal( SIGTERM, [ $this, 'shutDownBySignal' ] );
			pcntl_signal( SIGINT, [ $this, 'shutDownBySignal' ] );
		}
	}

	public function shutDownBySignal( int $signal ) : void
	{
		if ( in_array( $signal, [ SIGINT, SIGTERM, SIGKILL ], true ) )
		{
			$this->shutdown();
		}
	}

	public function shutdown() : void
	{
		$this->stop();
		$this->removeAllStreams();
	}

	private function waitForStreamActivity() : void
	{
		$activeReadStreams = $activeWriteStreams = [];

		try
		{
			$active = $this->streamSelect( $activeReadStreams, $activeWriteStreams );
		}
		catch ( RuntimeException $e )
		{
			return;
		}

		if ( $active === 0 )
		{
			return;
		}

		foreach ( $activeReadStreams as $stream )
		{
			$this->callStreamListener( $stream, $this->readStreamListeners );
		}

		foreach ( $activeWriteStreams as $stream )
		{
			$this->callStreamListener( $stream, $this->writeStreamListeners );
		}
	}

	/**
	 * @param array|resource[] $activeReadStreams
	 * @param array|resource[] $activeWriteStreams
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 * @return int
	 */
	private function streamSelect( array &$activeReadStreams, array &$activeWriteStreams ) : int
	{
		$readStreams  = $this->getRawStreams( $this->readStreams );
		$writeStreams = $this->getRawStreams( $this->writeStreams );

		usleep( self::STREAM_SELECT_TIMEOUT_USEC );

		if ( count( $readStreams ) + count( $writeStreams ) === 0 )
		{
			return 0;
		}

		$except = null;
		$active = @stream_select( $readStreams, $writeStreams, $except, 0, self::STREAM_SELECT_TIMEOUT_USEC );

		if ( false === $active )
		{
			throw new RuntimeException( 'Systemcall interrupted.' );
		}

		$activeReadStreams  = $this->shuffleStreams( array_intersect_key( $this->readStreams, $readStreams ) );
		$activeWriteStreams = $this->shuffleStreams( array_intersect_key( $this->writeStreams, $writeStreams ) );

		return $active;
	}

	/**
	 * @param array|TransfersData[] $streams
	 *
	 * @return array|\resource[]
	 */
	private function getRawStreams( array $streams ) : array
	{
		$rawStreams = [];

		foreach ( $streams as $stream )
		{
			$stream->collectRawStream( $rawStreams );
		}

		return $rawStreams;
	}

	private function shuffleStreams( array $activeStreams ) : array
	{
		$shuffledStreams = [];
		$keys            = array_keys( $activeStreams );

		shuffle( $keys );

		foreach ( $keys as $key )
		{
			$shuffledStreams[ $key ] = $activeStreams[ $key ];
		}

		return $shuffledStreams;
	}

	/**
	 * @param TransfersData                    $stream
	 * @param array|ListensForStreamActivity[] $listeners
	 */
	private function callStreamListener( TransfersData $stream, array $listeners ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		if ( isset( $listeners[ $streamId ] ) )
		{
			$listeners[ $streamId ]->handleStreamActivity( $stream, $this );
		}
	}

	public function stop() : void
	{
		$this->isRunning = false;
	}
}
