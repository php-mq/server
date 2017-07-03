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
	private $streams = [];

	/** @var array|ListensForStreamActivity[] */
	private $streamListeners = [];

	/** @var bool */
	private $isRunning = false;

	public function addStream( TransfersData $stream, ListensForStreamActivity $listener ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		$this->streams[ $streamId ]         = $stream;
		$this->streamListeners[ $streamId ] = $listener;
	}

	public function removeAllStreams() : void
	{
		foreach ( $this->streams as $stream )
		{
			$this->removeStream( $stream );
		}
	}

	public function removeStream( TransfersData $stream ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		$this->streams[ $streamId ]->shutDown();
		$this->streams[ $streamId ]->close();

		unset( $this->streams[ $streamId ], $this->streamListeners[ $streamId ] );
	}

	public function start() : void
	{
		$this->registerSignalHandler();

		$this->isRunning = (count( $this->streams ) > 0);

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

	private function shutDownBySignal( int $signal ) : void
	{
		if ( in_array( $signal, [ SIGINT, SIGTERM, SIGKILL ], true ) )
		{
			$this->shutdown();
			exit( 0 );
		}
	}

	public function shutdown() : void
	{
		$this->stop();
		$this->removeAllStreams();
	}

	private function waitForStreamActivity() : void
	{
		try
		{
			$activeStreams = $this->getActiveStreams( self::STREAM_SELECT_TIMEOUT_USEC );
		}
		catch ( RuntimeException $e )
		{
			return;
		}

		foreach ( $activeStreams as $stream )
		{
			$this->callStreamListener( $stream );
		}
	}

	/**
	 * @param int|null $timeout
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 * @return array|TransfersData[]
	 */
	private function getActiveStreams( ?int $timeout ) : array
	{
		$rawStreams = $this->getRawStreams();

		if ( 0 === count( $rawStreams ) )
		{
			$timeout && usleep( $timeout );

			return [];
		}

		$write  = $except = null;
		$active = @stream_select( $rawStreams, $write, $except, $timeout === null ? null : 0, $timeout );

		if ( false === $active )
		{
			throw new RuntimeException( 'Systemcall interrupted.' );
		}

		return array_intersect_key( $this->streams, $rawStreams );
	}

	/**
	 * @return array|resource[]
	 */
	private function getRawStreams() : array
	{
		$streams = [];

		foreach ( $this->streams as $stream )
		{
			$stream->collectRawStream( $streams );
		}

		return $streams;
	}

	private function callStreamListener( TransfersData $stream ) : void
	{
		$streamId = $stream->getStreamId()->toString();

		if ( isset( $this->streamListeners[ $streamId ] ) )
		{
			$this->streamListeners[ $streamId ]->handleStreamActivity( $stream, $this );
		}
	}

	public function stop() : void
	{
		$this->isRunning = false;
	}
}
