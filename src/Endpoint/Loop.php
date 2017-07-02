<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Exceptions\RuntimeException;

/**
 * Class Loop
 * @package PHPMQ\Server\Endpoint
 */
final class Loop implements TracksStreams
{
	/** @var array */
	private $readStreams = [];

	/** @var array */
	private $readStreamListeners = [];

	/** @var array */
	private $writeStreams = [];

	/** @var array */
	private $writeStreamListeners = [];

	/** @var bool */
	private $isRunning = false;

	public function addReadStream( $stream, callable $listener ) : void
	{
		$streamId = (int)$stream;

		$this->readStreams[ $streamId ]         = $stream;
		$this->readStreamListeners[ $streamId ] = $listener;
	}

	public function addWriteStream( $stream, callable $listener ) : void
	{
		$streamId = (int)$stream;

		$this->writeStreams[ $streamId ]         = $stream;
		$this->writeStreamListeners[ $streamId ] = $listener;
	}

	public function removeAllStreams() : void
	{
		$this->readStreams          = [];
		$this->readStreamListeners  = [];
		$this->writeStreams         = [];
		$this->writeStreamListeners = [];
	}

	public function removeStream( $stream ) : void
	{
		$this->removeReadStream( $stream );
		$this->removeWriteStream( $stream );
	}

	private function removeReadStream( $stream ) : void
	{
		$streamId = (int)$stream;
		unset( $this->readStreams[ $streamId ], $this->readStreamListeners[ $streamId ] );
	}

	private function removeWriteStream( $stream ) : void
	{
		$streamId = (int)$stream;
		unset( $this->writeStreams[ $streamId ], $this->writeStreamListeners[ $streamId ] );
	}

	public function start() : void
	{
		$this->registerSignalHandler();

		$this->isRunning = true;

		declare(ticks=1);

		while ( $this->isRunning )
		{
			if ( empty( $this->readStreams ) && empty( $this->writeStreams ) )
			{
				break;
			}

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
		$readStreams  = $this->readStreams;
		$writeStreams = $this->writeStreams;

		try
		{
			$active = $this->streamSelect( $readStreams, $writeStreams, 200000 );
		}
		catch ( RuntimeException $e )
		{
			return;
		}

		if ( 0 === $active )
		{
			return;
		}

		foreach ( $readStreams as $readStream )
		{
			$this->callStreamListener( $readStream, $this->readStreamListeners );
		}

		foreach ( $writeStreams as $writeStream )
		{
			$this->callStreamListener( $writeStream, $this->writeStreamListeners );
		}
	}

	private function streamSelect( array &$read, array &$write, ?int $timeout ) : int
	{
		if ( empty( $read ) && empty( $write ) )
		{
			$timeout && usleep( $timeout );

			return 0;
		}

		$except = null;

		$active = @stream_select( $read, $write, $except, $timeout === null ? null : 0, $timeout );

		if ( false === $active )
		{
			throw new RuntimeException( 'Systemcall interrupted.' );
		}

		return $active;
	}

	private function callStreamListener( $stream, array $listeners ) : void
	{
		$streamId = (int)$stream;

		if ( isset( $listeners[ $streamId ] ) )
		{
			call_user_func( $listeners[ $streamId ], $stream, $this );
		}
	}

	public function stop() : void
	{
		$this->isRunning = false;
	}
}
