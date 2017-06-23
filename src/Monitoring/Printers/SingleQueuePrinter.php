<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Printers;

use PHPMQ\Server\Monitoring\Formatters\ByteFormatter;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesMonitoringInfo;
use PHPMQ\Server\Types\QueueInfo;
use PHPMQ\Server\Types\QueueName;

/**
 * Class SingleQueuePrinter
 * @package PHPMQ\Server\Monitoring\Printers
 */
final class SingleQueuePrinter extends AbstractPrinter
{
	/** @var QueueName */
	private $queueName;

	public function __construct( QueueName $queueName )
	{
		$this->queueName = $queueName;
	}

	public function print( ProvidesMonitoringInfo $monitoringInfo ): void
	{
		$this->clearScreen();
		$this->updateTerminalWidth();
		$this->updateTerminalHeight();
		$queueInfo = $monitoringInfo->getQueueInfo( $this->queueName );
		$this->printHeader( $monitoringInfo, $queueInfo );
		$this->printMessages( $queueInfo );
	}

	private function printHeader( ProvidesMonitoringInfo $monitoringInfo, QueueInfo $queueInfo ): void
	{
		printf(
			"\e[44m Queue name:\e[43m %s \e[44m  Message count:\e[43m %4d \r\n\e[44m Size:\e[43m %4d \e[44m  Clients connected:\e[43m %4d \e[0m\r\n\r\n",
			$this->queueName->toString(),
			$queueInfo->getMessageCount(),
			(new ByteFormatter( $queueInfo->getSize() ))->format( 0 ),
			$monitoringInfo->getConnectedClientsCount()
		);
	}

	private function printMessages( QueueInfo $queueInfo ): void
	{
		$index        = 0;
		$messageCount = $queueInfo->getMessageCount();
		foreach ( $queueInfo->getMessageInfos() as $messageInfo )
		{
			$line = sprintf(
				'%5d  %s  %s  %5s  %s',
				++$index,
				(string)$messageInfo['messageId'],
				(bool)$messageInfo['dispatched'] ? '→' : '•',
				(new ByteFormatter( (int)$messageInfo['size'] ))->format( 0 ),
				date( 'Y-m-d H:i:s', (int)($messageInfo['createdAt'] / 10000) )
			);

			if ( strlen( $line ) > $this->getTerminalWidth() - 4 )
			{
				$line = substr( $line, 0, $this->getTerminalWidth() - 4 ) . '...';
			}

			echo $line . "\r\n";

			if ( $index === $this->getTerminalHeight() - 6 )
			{
				echo '... (' . ($messageCount - $index) . ' more)';
				break;
			}
		}
	}
}
