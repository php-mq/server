<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitoring;

use PHPMQ\Protocol\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Interfaces\CreatesMonitoringOutput;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Printers\OverviewPrinter;
use PHPMQ\Server\Monitoring\Printers\SingleQueuePrinter;
use PHPMQ\Server\StreamListeners\Interfaces\RefreshesMonitoringInformation;

/**
 * Class ServerMonitor
 * @package PHPMQ\Server\Monitoring
 */
final class ServerMonitor implements RefreshesMonitoringInformation
{
	private const REFRESH_INTERVAL = 0.5;

	/** @var ProvidesServerMonitoringInfo */
	private $serverMonitoringInfo;

	/** @var float */
	private $lastRefresh = 0;

	/** @var PreparesOutputForCli */
	private $cliWriter;

	public function __construct( ProvidesServerMonitoringInfo $serverMonitoringInfo, PreparesOutputForCli $cliWriter )
	{
		$this->serverMonitoringInfo = $serverMonitoringInfo;
		$this->cliWriter            = $cliWriter;
	}

	public function refresh( IdentifiesQueue $queueName, TransfersData $stream ) : void
	{
		$microtime = microtime( true );

		if ( $this->lastRefresh <= microtime( true ) - self::REFRESH_INTERVAL )
		{
			$this->lastRefresh = $microtime;

			$this->processMonitoringRequest( $queueName, $stream );
		}
	}

	private function processMonitoringRequest( IdentifiesQueue $queueName, TransfersData $stream ) : void
	{
		$printer = $this->getPrinter( $queueName );

		$stream->write( $printer->getOutput( $this->serverMonitoringInfo ) );
	}

	private function getPrinter( IdentifiesQueue $queueName ) : CreatesMonitoringOutput
	{
		if ( $queueName->toString() !== '' )
		{
			return new SingleQueuePrinter( $this->cliWriter, $queueName );
		}

		return new OverviewPrinter( $this->cliWriter );
	}
}
