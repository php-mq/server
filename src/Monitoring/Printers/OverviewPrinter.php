<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Printers;

use PHPMQ\Server\Monitoring\Formatters\ByteFormatter;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesMonitoringInfo;
use PHPMQ\Server\Types\QueueInfo;

/**
 * Class OverviewPrinter
 * @package PHPMQ\Server\Monitoring\Printers
 */
final class OverviewPrinter extends AbstractPrinter
{
	private const ROW_COLOR           = "\e[0m";

	private const ROW_COLOR_ALTERNATE = "\e[2m";

	private const VISUAL_QUEUE_LIMIT  = 500;

	public function print( ProvidesMonitoringInfo $monitoringInfo ): void
	{
		$this->clearScreen();
		$this->updateTerminalWidth();
		$this->printHeader( $monitoringInfo );
		$this->printQueueInfos( $monitoringInfo );
	}

	private function printHeader( ProvidesMonitoringInfo $monitoringInfo ): void
	{
		printf(
			"\e[44m Queues total:\e[43m %4d \e[44m  Clients connected:\e[43m %4d \e[0m\r\n\r\n",
			$monitoringInfo->getQueueCount(),
			$monitoringInfo->getConnectedClientsCount()
		);
		printf( "%-20s %5s %10s  Workload\r\n", 'Queue', 'Msgs', 'Size' );
	}

	private function printQueueInfos( ProvidesMonitoringInfo $monitoringInfo ): void
	{
		$alternate = false;
		foreach ( $monitoringInfo->getQueueInfos() as $queueInfo )
		{
			$alternate ? $this->drawQueueAlternate( $queueInfo ) : $this->drawQueueNormal( $queueInfo );
			$alternate ^= true;
		}
	}

	private function drawQueueAlternate( QueueInfo $queueInfo ): void
	{
		$this->drawQueue( $queueInfo, self::ROW_COLOR_ALTERNATE );
	}

	private function drawQueue( QueueInfo $queueInfo, string $rowColor ): void
	{
		$messageCount = $queueInfo->getMessageCount();

		printf(
			"%s%-20s %5d %10s  %s\e[0m\r\n",
			$rowColor,
			$queueInfo->getQueueName(),
			$messageCount,
			(new ByteFormatter( $queueInfo->getSize() ))->format( 0 ),
			str_repeat( '█', (int)(($this->getTerminalWidth() - 39) * ($messageCount / self::VISUAL_QUEUE_LIMIT)) )
		);
	}

	private function drawQueueNormal( QueueInfo $queueInfo ): void
	{
		$this->drawQueue( $queueInfo, self::ROW_COLOR );
	}
}
