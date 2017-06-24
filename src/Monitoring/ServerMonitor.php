<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitoring;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Interfaces\PreparesOutputForCli;
use PHPMQ\Server\Monitoring\Interfaces\CreatesMonitoringOutput;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Printers\OverviewPrinter;
use PHPMQ\Server\Monitoring\Printers\SingleQueuePrinter;
use PHPMQ\Server\Monitoring\Types\MonitoringRequest;

/**
 * Class ServerMonitor
 * @package PHPMQ\Server\Monitoring
 */
final class ServerMonitor
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

	public function refresh() : void
	{
		if ( !$this->serverMonitoringInfo->hasMonitoringRequests() )
		{
			return;
		}

		$microtime = microtime( true );

		if ( $this->lastRefresh <= microtime( true ) - self::REFRESH_INTERVAL )
		{
			$this->lastRefresh = $microtime;

			$this->processMonitoringRequests();
		}
	}

	private function processMonitoringRequests() : void
	{
		foreach ( $this->serverMonitoringInfo->getMonitoringRequests() as $monitoringRequest )
		{
			$this->processMonitoringRequest( $monitoringRequest );
		}
	}

	private function processMonitoringRequest( MonitoringRequest $monitoringRequest ) : void
	{
		$client    = $monitoringRequest->getMaintenanceClient();
		$queueName = $monitoringRequest->getQueueName();
		$printer   = $this->getPrinter( $queueName );

		$client->write( $printer->getOutput( $this->serverMonitoringInfo ) );
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
