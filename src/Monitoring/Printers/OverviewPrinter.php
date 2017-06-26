<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Printers;

use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Formatters\ByteFormatter;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Types\QueueInfo;

/**
 * Class OverviewPrinter
 * @package PHPMQ\Server\Printers
 */
final class OverviewPrinter extends AbstractPrinter
{
	/** @var ByteFormatter */
	private $byteFormatter;

	public function __construct( PreparesOutputForCli $cliWriter )
	{
		parent::__construct( $cliWriter );
		$this->byteFormatter = new ByteFormatter();
	}

	public function getOutput( ProvidesServerMonitoringInfo $serverMonitoringInfo ): string
	{
		$this->getCliWriter()->clearScreen(
			sprintf(
				'OVERVIEW-MONITOR | Memory: %s (current) / %s (peak)',
				$this->byteFormatter->format( memory_get_usage( true ), 0 ),
				$this->byteFormatter->format( memory_get_peak_usage( true ), 0 )
			)
		);

		$this->addHeader( $serverMonitoringInfo );
		$this->addQueueInfos( $serverMonitoringInfo );

		return $this->getCliWriter()->getOutput();
	}

	private function addHeader( ProvidesServerMonitoringInfo $monitoringInfo ): void
	{
		$this->getCliWriter()->writeLn( 'Type "q"+ENTER to quit the monitor.' );
		$this->getCliWriter()->writeLn( '' );
		$this->getCliWriter()->writeLn(
			'<bg:blue> Queues total:<bg:yellow> %4d <bg:blue> Clients connected:<bg:yellow> %4d <:bg>',
			(string)$monitoringInfo->getQueueCount(),
			(string)$monitoringInfo->getConnectedClientsCount()
		);
		$this->getCliWriter()->writeLn( '' );
		$this->getCliWriter()->writeLn( '<fg:yellow>%-20s %5s %10s  Workload<:fg>', 'Queue', 'Msgs', 'Size' );
	}

	private function addQueueInfos( ProvidesServerMonitoringInfo $monitoringInfo ): void
	{
		$index          = 0;
		$alternate      = false;
		$queueCount     = $monitoringInfo->getQueueCount();
		$terminalHeight = $this->getCliWriter()->getTerminalHeight() - 11;
		$maxQueueSize   = $monitoringInfo->getMaxQueueSize();

		foreach ( $monitoringInfo->getQueueInfos() as $queueInfo )
		{
			$index++;
			$alternate ^= true;
			$queueLine = $this->getQueueLine( $queueInfo, $maxQueueSize );

			if ( $alternate )
			{
				$queueLine = sprintf( '<alt:>%s<:alt>', $queueLine );
			}

			$this->getCliWriter()->writeLn( $queueLine );

			if ( $index === $terminalHeight )
			{
				$this->getCliWriter()->writeLn( '... (%d more)', (string)($queueCount - $index) );
				break;
			}
		}
	}

	private function getQueueLine( QueueInfo $queueInfo, int $maxQueueSize ): string
	{
		$messageCount  = $queueInfo->getMessageCount();
		$terminalWidth = $this->getCliWriter()->getTerminalWidth() - 39;

		return sprintf(
			'%-20s %5d %10s  %s',
			$queueInfo->getQueueName(),
			$messageCount,
			$this->byteFormatter->format( $queueInfo->getSize(), 0 ),
			str_repeat( '█', (int)($terminalWidth * (($messageCount * 2) / ($maxQueueSize * 3))) )
		);
	}
}
