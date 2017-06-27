<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Printers;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Formatters\ByteFormatter;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Types\QueueInfo;

/**
 * Class SingleQueuePrinter
 * @package PHPMQ\Server\Printers
 */
final class SingleQueuePrinter extends AbstractPrinter
{
	/** @var IdentifiesQueue */
	private $queueName;

	/** @var ByteFormatter */
	private $byteFormatter;

	public function __construct( PreparesOutputForCli $cliWriter, IdentifiesQueue $queueName )
	{
		parent::__construct( $cliWriter );
		$this->queueName     = $queueName;
		$this->byteFormatter = new ByteFormatter();
	}

	public function getOutput( ProvidesServerMonitoringInfo $serverMonitoringInfo ) : string
	{
		$memoryUsage = memory_get_peak_usage( true );
		$this->getCliWriter()->clearScreen(
			sprintf(
				'QUEUE-MONITOR | Memory: %s (current) / %s (peak)',
				$this->byteFormatter->format( memory_get_usage( true ), 0 ),
				$this->byteFormatter->format( memory_get_peak_usage( true ), 0 )
			)
		);

		$this->getCliWriter()->writeLn( 'Type "q"+ENTER to quit the monitor.' );
		$this->getCliWriter()->writeLn( '' );

		$queueInfo = $serverMonitoringInfo->getQueueInfo( $this->queueName );

		$this->addQueueInfo( $queueInfo );
		$this->addConnectedClients( $serverMonitoringInfo );
		$this->addMessages( $queueInfo );

		return $this->getCliWriter()->getOutput();
	}

	private function addQueueInfo( QueueInfo $queueInfo ) : void
	{
		$this->getCliWriter()->writeLn(
			'<bg:blue> Queue name:<bg:yellow> %s <bg:blue>  Message count:<bg:yellow> %4d <bg:blue> Size:<bg:yellow> %s <:bg>',
			$this->queueName->toString(),
			(string)$queueInfo->getMessageCount(),
			(new ByteFormatter())->format( $queueInfo->getSize(), 0 )
		);
	}

	private function addConnectedClients( ProvidesServerMonitoringInfo $serverMonitoringInfo ) : void
	{
		$this->getCliWriter()->writeLn(
			'<bg:blue> Clients connected:<bg:yellow> %4d <:bg>',
			(string)$serverMonitoringInfo->getConnectedClientsCount()
		);
	}

	private function addMessages( QueueInfo $queueInfo ) : void
	{
		$index        = 0;
		$alternate    = false;
		$messageCount = $queueInfo->getMessageCount();

		$terminalWidth  = $this->getCliWriter()->getTerminalWidth() - 4;
		$terminalHeight = $this->getCliWriter()->getTerminalHeight() - 10;

		foreach ( $queueInfo->getMessageInfos() as $messageInfo )
		{
			$alternate ^= true;

			$line = sprintf(
				'%5d  %s  %s  %5s  %s',
				++$index,
				(string)$messageInfo['messageId'],
				(bool)$messageInfo['dispatched'] ? '→' : '•',
				$this->byteFormatter->format( (int)$messageInfo['size'], 0 ),
				date( 'Y-m-d H:i:s', (int)($messageInfo['createdAt'] / 10000) )
			);

			if ( mb_strlen( $line ) > $terminalWidth )
			{
				$line = mb_substr( $line, 0, $terminalWidth ) . '...';
			}

			if ( $alternate )
			{
				$line = sprintf( '<alt:>%s<:alt>', $line );
			}

			$this->getCliWriter()->writeLn( $line );

			if ( $index === $terminalHeight )
			{
				$this->getCliWriter()->writeLn( '... (%d more)', (string)($messageCount - $index) );
				break;
			}
		}
	}
}
