<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Endpoint;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Loop;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Stream\Interfaces\TransfersData;
use PHPMQ\Stream\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareTrait;

/**
 * Class LoopTest
 * @package PHPMQ\Server\Tests\Unit\Endpoint
 */
final class LoopTest extends TestCase
{
	use SocketMocking;

	protected function setUp() : void
	{
		$this->setUpServerSocket();
	}

	protected function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	public function testWontStartIfNoReadStreamsAdded() : void
	{
		$loop = new Loop();
		$loop->start();

		$this->assertTrue( true );
	}

	public function testCanAddReadStreams() : void
	{
		$loop     = new Loop();
		$stream   = new Stream( $this->serverSocket );
		$listener = new class implements ListensForStreamActivity
		{
			use LoggerAwareTrait;

			public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
			{
				echo 'OK';

				$loop->shutdown();
			}
		};

		$loop->addReadStream( $stream, $listener );

		$remoteStream = new Stream( $this->getRemoteClientSocket() );

		$loop->start();

		$this->expectOutputString( 'OK' );

		$remoteStream->close();
	}

	public function testCanAddWriteStream() : void
	{
		$loop     = new Loop();
		$stream   = new Stream( $this->serverSocket );
		$listener = new class implements ListensForStreamActivity
		{
			use LoggerAwareTrait;

			public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
			{
				$stream->write( 'OK' );

				$loop->removeWriteStream( $stream );
				$loop->stop();
			}
		};

		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop->addWriteStream( $clientStream, $listener );

		$loop->start();

		$this->assertSame( 'OK', $remoteStream->read( 1024 ) );

		$clientStream->close();
		$remoteStream->close();
	}

	public function testCanRemoveAllStreams() : void
	{
		$loop     = new Loop();
		$stream   = new Stream( $this->serverSocket );
		$listener = new class implements ListensForStreamActivity
		{
			use LoggerAwareTrait;

			public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
			{
				$loop->removeAllStreams();
				$loop->stop();

				echo 'OK';
			}
		};

		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop->addReadStream( $stream, $listener );
		$loop->addWriteStream( $clientStream, $listener );

		$loop->start();

		$this->expectOutputString( 'OK' );

		$clientStream->close();
		$remoteStream->close();
	}

	public function testCanRemoveStreamFromReadAndWrite() : void
	{
		$loop     = new Loop();
		$stream   = new Stream( $this->serverSocket );
		$listener = new class implements ListensForStreamActivity
		{
			use LoggerAwareTrait;

			public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
			{
				$loop->removeStream( $stream );
				$loop->stop();

				echo 'OK';
			}
		};

		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop->addReadStream( $clientStream, $listener );
		$loop->addWriteStream( $clientStream, $listener );

		$loop->start();

		$this->expectOutputString( 'OK' );

		$remoteStream->close();
	}

	/**
	 * @requires     pcntl
	 *
	 * @param int $handlerSignal
	 * @param int $executeSignal
	 *
	 * @dataProvider signalProvider
	 */
	public function testLoopRegistersShutdownHandlerForSignals( int $handlerSignal, int $executeSignal ) : void
	{
		$loop     = new Loop();
		$stream   = new Stream( $this->serverSocket );
		$listener = new class($handlerSignal, $executeSignal) implements ListensForStreamActivity
		{
			use LoggerAwareTrait;

			/** @var int */
			private $handlerSignal;

			/** @var int */
			private $executeSignal;

			public function __construct( int $handlerSignal, int $executeSignal )
			{
				$this->handlerSignal = $handlerSignal;
				$this->executeSignal = $executeSignal;
			}

			public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
			{
				$handler = pcntl_signal_get_handler( $this->handlerSignal );

				/** @var callable $handler */
				$handler( $this->executeSignal );

				echo 'Shutdown';
			}
		};

		$remoteStream = new Stream( $this->getRemoteClientSocket() );

		$loop->addReadStream( $stream, $listener );

		$loop->start();

		$this->expectOutputString( 'Shutdown' );

		$remoteStream->close();
	}

	public function signalProvider() : array
	{
		return [
			[
				'handlerSignal' => SIGTERM,
				'executeSignal' => SIGTERM,
			],
			[
				'handlerSignal' => SIGTERM,
				'executeSignal' => SIGKILL,
			],
			[
				'handlerSignal' => SIGINT,
				'executeSignal' => SIGINT,
			],
			[
				'handlerSignal' => SIGINT,
				'executeSignal' => SIGKILL,
			],
		];
	}
}
