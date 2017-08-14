<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Monitoring\Printers;

use PHPMQ\Server\CliWriter;
use PHPMQ\Server\Monitoring\Printers\SingleQueuePrinter;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Types\Message;
use PHPUnit\Framework\TestCase;

/**
 * Class SingleQueuePrinterTest
 * @package PHPMQ\Server\Tests\Unit\Monitoring\Printers
 */
final class SingleQueuePrinterTest extends TestCase
{
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	public function testCanGetOutputForEmptyQueue() : void
	{
		$cliWriter            = new CliWriter( '1.2.3' );
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$printer              = new SingleQueuePrinter( $cliWriter, $queueName );
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$output = $printer->getOutput( $serverMonitoringInfo );

		$this->assertRegExp( '#QUEUE\-MONITOR#', $output );
		$this->assertRegExp( '#Queue name\:.+Test\-Queue.+#', $output );
		$this->assertRegExp( '#Message count\:.+   0 .+#', $output );
		$this->assertRegExp( '#Size\:.+ 0 B .+#', $output );
	}

	public function testCanGetOutputForQueueWithMessages() : void
	{
		$cliWriter            = new CliWriter( '1.2.3' );
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$printer              = new SingleQueuePrinter( $cliWriter, $queueName );
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$messageCount         = 24;

		for ( $i = 0; $i < $messageCount; $i++ )
		{
			$serverMonitoringInfo->addMessage(
				$queueName,
				new Message(
					$this->getMessageId( 'Very-Loooooooooooong-ID-With-A-Number-' . $i ),
					'Test-' . $i
				)
			);
		}

		$output = $printer->getOutput( $serverMonitoringInfo );

		$this->assertRegExp( '#QUEUE\-MONITOR#', $output );
		$this->assertRegExp( '#Queue name\:.+Test\-Queue.+#', $output );
		$this->assertRegExp( '#Message count\:.+   24 .+#', $output );
		$this->assertRegExp( '#Size\:.+ 158 B .+#', $output );
		$this->assertRegExp( '#\(10 more\)#', $output );
	}
}
