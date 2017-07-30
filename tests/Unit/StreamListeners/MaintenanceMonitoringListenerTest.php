<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\StreamListeners\Interfaces\RefreshesMonitoringInformation;
use PHPMQ\Server\StreamListeners\MaintenanceMonitoringListener;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Stream\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Class MaintenanceMonitoringListenerTest
 * @package PHPMQ\Server\Tests\Unit\StreamListeners
 */
final class MaintenanceMonitoringListenerTest extends TestCase
{
	use SocketMocking;
	use QueueIdentifierMocking;

	protected function setUp() : void
	{
		$this->setUpServerSocket();
	}

	protected function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	public function testCanRefreshMonitor() : void
	{
		$queueName     = $this->getQueueName( 'Test-Queue' );
		$serverStream  = new Stream( $this->serverSocket );
		$remoteStream  = new Stream( $this->getRemoteClientSocket() );
		$clientStream  = $serverStream->acceptConnection();
		$serverMonitor = $this->getMockForAbstractClass( RefreshesMonitoringInformation::class );

		$serverMonitor->expects( $this->once() )->method( 'refresh' )->with( $queueName, $clientStream );

		$loop = $this->getMockForAbstractClass( TracksStreams::class );

		/** @var RefreshesMonitoringInformation $serverMonitor */
		$listener = new MaintenanceMonitoringListener( $queueName, $serverMonitor );

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$clientStream->close();
		$remoteStream->close();
	}
}
