<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Streams;

use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StreamIdentifierMocking;
use PHPUnit\Framework\TestCase;

final class StreamTest extends TestCase
{
	use SocketMocking;
	use StreamIdentifierMocking;

	public function setUp() : void
	{
		$this->setUpServerSocket();
	}

	public function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	public function testCanGetStreamId() : void
	{
		$stream           = new Stream( $this->serverSocket );
		$expectedStreamId = $this->getStreamId( (string)$this->serverSocket );

		$this->assertTrue( $expectedStreamId->equals( $stream->getStreamId() ) );
	}

	public function testCanAcceptClientStream() : void
	{
		$stream = new Stream( $this->serverSocket );

		$this->getRemoteClientSocket();

		$client = $stream->acceptConnection();

		$this->assertInstanceOf( Stream::class, $client );
	}

	public function testAcceptingClientStreamReturnNullIfNonConnected() : void
	{
		$stream = new Stream( $this->serverSocket );
		$client = $stream->acceptConnection();

		$this->assertNull( $client );
	}

	public function testCanCheckForUnreadBytes() : void
	{
		$stream       = new Stream( $this->serverSocket );
		$remoteClient = new Stream( $this->getRemoteClientSocket() );
		$client       = $stream->acceptConnection();

		$remoteClient->write( 'Unit-Test' );

		$unit = $client->read( 4 );

		$this->assertSame( 'Unit', $unit );

		$this->assertTrue( $client->hasUnreadBytes() );
	}

	public function testCanCollectRawStream() : void
	{
		$stream             = new Stream( $this->serverSocket );
		$expectedRawStreams = [(string)$this->serverSocket => $this->serverSocket];

		$rawStreams = [];
		$stream->collectRawStream( $rawStreams );

		$this->assertSame( $expectedRawStreams, $rawStreams );
	}

	public function testCanShutdownStream() : void
	{
		$stream = new Stream( $this->serverSocket );

		$this->getRemoteClientSocket();

		$client = $stream->acceptConnection();

		$client->shutDown();

		$bytesWritten = $client->write( 'Unit-Test' );

		$this->assertSame( 0, $bytesWritten );
	}

	public function testCanCloseStream() : void
	{
		$stream = new Stream( $this->serverSocket );

		$stream->close();

		/** @var bool $metaData */
		$metaData = @stream_get_meta_data( $this->serverSocket );

		$this->assertFalse( $metaData );
	}

	public function testCanReadWriteChunked() : void
	{
		$stream        = new Stream( $this->serverSocket );
		$remoteClient  = new Stream( $this->getRemoteClientSocket() );
		$client        = $stream->acceptConnection();
		$message       = 'Unit-Test-Message';
		$messageLength = strlen( $message );

		$written = $remoteClient->writeChunked( $message, 4 );

		$this->assertSame( $messageLength, $written );

		$read = $client->readChunked( $messageLength, 4 );

		$this->assertSame( $message, $read );
	}

	/**
	 * @expectedException \PHPMQ\Server\Streams\Exceptions\ReadTimedOutException
	 */
	public function testReadingChunkedCanTimeOut() : void
	{
		$stream        = new Stream( $this->serverSocket );
		$remoteClient  = new Stream( $this->getRemoteClientSocket() );
		$client        = $stream->acceptConnection();
		$message       = 'Unit-Test-Message';
		$messageLength = strlen( $message );

		$written = $remoteClient->writeChunked( $message, 4 );

		$this->assertSame( $messageLength, $written );

		$client->readChunked( $messageLength + 1, 4 );
	}

	/**
	 * @expectedException \PHPMQ\Server\Streams\Exceptions\WriteTimedOutException
	 */
	public function testWritingChunkedCanTimeOut() : void
	{
		$stream       = new Stream( $this->serverSocket );
		$remoteClient = new Stream( $this->getRemoteClientSocket() );
		$client       = $stream->acceptConnection();
		$message      = 'Unit-Test-Message';

		$client->shutDown();

		$remoteClient->writeChunked( $message, 4 );
	}
}
