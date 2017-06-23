<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Monitoring;

use PHPMQ\Server\Monitoring\Interfaces\PrintsMonitoringInfo;
use PHPMQ\Server\Monitoring\Interfaces\ProvidesMonitoringInfo;
use PHPMQ\Server\Monitoring\Printers\NullPrinter;
use PHPMQ\Server\Monitoring\Printers\OverviewPrinter;
use PHPMQ\Server\Monitoring\Printers\SingleQueuePrinter;
use PHPMQ\Server\Monitoring\Types\ServerMonitoringConfig;
use PHPMQ\Server\Types\QueueName;

/**
 * Class ServerMonitor
 * @package PHPMQ\Server\Monitoring
 */
final class ServerMonitor
{
	private const REFRESH_INTERVAL = 0.5;

	/** @var ServerMonitoringConfig */
	private $config;

	/** @var ProvidesMonitoringInfo */
	private $monitoringInfo;

	/** @var PrintsMonitoringInfo */
	private $printer;

	/** @var float */
	private $lastRefresh = 0;

	public function __construct( ServerMonitoringConfig $config, ProvidesMonitoringInfo $monitoringInfo )
	{
		$this->config         = $config;
		$this->monitoringInfo = $monitoringInfo;
		$this->printer        = $this->getPrinter();
	}

	private function getPrinter(): PrintsMonitoringInfo
	{
		if ( $this->config->isDisabled() )
		{
			return new NullPrinter();
		}

		if ( $this->config->getQueueName() !== '' )
		{
			return new SingleQueuePrinter( new QueueName( $this->config->getQueueName() ) );
		}

		return new OverviewPrinter();
	}

	public function refresh(): void
	{
		$microtime = microtime( true );

		if ( $this->lastRefresh <= microtime( true ) - self::REFRESH_INTERVAL )
		{
			$this->printer->print( $this->monitoringInfo );
			$this->lastRefresh = $microtime;
		}
	}
}
