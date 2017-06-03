<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitors;

use PHPMQ\Server\Loggers\Types\ServerMonitoringInfo;

/**
 * Class ServerMonitor
 * @package PHPMQ\Server\Monitors
 */
final class ServerMonitor
{
	private const TERMINAL_WIDTH_FALLBACK  = 80;

	private const TERMINAL_HEIGHT_FALLBACK = 60;

	public function refresh( ServerMonitoringInfo $monitoringInfo ) : void
	{
		$outputData = [];

		$memoryUsage = memory_get_usage( true ) / 1024 / 1024;

		$outputData[] = sprintf(
			"Connected clients: \e[43m %s \e[0m | Memory usage: \e[41m %01.2f MB \e[0m",
			$monitoringInfo->getConnectedClientsCount(),
			$memoryUsage
		);
		$outputData[] = '';

		$countQueues = 0;

		foreach ( $monitoringInfo->getQueueInfos() as $queueInfo )
		{
			++$countQueues;

			$outputData[] = sprintf(
				"\e[44mQueue: \e[41m %s \e[44m (Size: \e[43m %d \e[44m)\e[0m",
				$queueInfo->getQueueName(),
				$queueInfo->getSize()
			);

			$index = 0;
			foreach ( $queueInfo->getMessageInfos() as $messageInfo )
			{
				$outputData[] = sprintf(
					"%03d\t%s\t%s\t%d byte\t%s",
					++$index,
					(string)$messageInfo['messageId'],
					(bool)$messageInfo['dispatched'] ? "\e[42m DISPATCHED \e[0m" : "\e[41m UNDISPATCHED \e[0m",
					(int)$messageInfo['size'],
					date( 'Y-m-d H:i:s', (int)($messageInfo['createdAt'] / 10000) )
				);
			}

			$outputData[] = '';
		}

		if ( $countQueues === 0 )
		{
			$outputData[] = 'NO QUEUES ACTIVE';
		}

		$this->writeMonitor( $outputData );
	}

	private function writeMonitor( array $outputData ) : void
	{
		passthru( 'clear' );

		[ $width, $height ] = $this->getTerminalWidthAndHeight();

		foreach ( $outputData as $index => $output )
		{
			if ( $index > $height - 4 )
			{
				echo "...\n";
				break;
			}

			if ( strlen( $output ) > $width - 1 )
			{
				echo substr( $output, 0, $width - 5 ) . "...\n";
				continue;
			}

			echo $output . "\n";
		}

		flush();
	}

	private function getTerminalWidthAndHeight() : array
	{
		$terminalWidth  = exec( 'tput cols', $toss, $statusWidth );
		$terminalHeight = exec( 'tput lines', $toss, $statusHeight );

		if ( $statusWidth )
		{
			$terminalWidth = self::TERMINAL_WIDTH_FALLBACK;
		}

		if ( $statusHeight )
		{
			$terminalWidth = self::TERMINAL_HEIGHT_FALLBACK;
		}

		return [ (int)$terminalWidth, (int)$terminalHeight ];
	}
}
