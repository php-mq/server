<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Monitoring\Printers;

use PHPMQ\Server\CliWriter;
use PHPMQ\Server\Monitoring\Printers\OverviewPrinter;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Types\Message;
use PHPUnit\Framework\TestCase;

/**
 * Class OverviewPrinterTest
 * @package PHPMQ\Server\Tests\Unit\Monitoring\Printers
 */
final class OverviewPrinterTest extends TestCase
{
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	public function testCanGetOutputForEmptyQueueList() : void
	{
		$cliWriter            = new CliWriter( '1.2.3' );
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$printer              = new OverviewPrinter( $cliWriter );

		$output = $printer->getOutput( $serverMonitoringInfo );

		$this->assertRegExp(
			'#OVERVIEW\-MONITOR#',
			$output
		);

		$this->assertRegExp(
			'#Queues total\:.+    0 .+#',
			$output
		);
	}

	public function testCanGetOutputWithQueues() : void
	{
		$cliWriter            = new CliWriter( '1.2.3' );
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$printer              = new OverviewPrinter( $cliWriter );
		$queueCount           = 24;

		for ( $i = 0; $i < $queueCount; $i++ )
		{
			$serverMonitoringInfo->addMessage(
				$this->getQueueName( 'Test-Queue-' . $i ),
				new Message(
					$this->getMessageId( 'Unit-Test-ID-' . $i ),
					'Unit-Test-' . $i
				)
			);
		}

		$output = $printer->getOutput( $serverMonitoringInfo );

		$this->assertRegExp( "#Queues total\:.+   {$queueCount} .+#", $output );

		for ( $i = 0; $i < $queueCount; $i++ )
		{
			$this->assertRegExp( "#Test\-Queue\-{$i}#", $output );

			if ( $i === 12 )
			{
				break;
			}
		}

		$this->assertRegExp( "#\(11 more\)#", $output );
	}
}
