<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers;

use PHPMQ\Server\Loggers\Constants\ServerMonitoring;
use PHPMQ\Server\Loggers\Types\MonitoringConfig;
use PHPMQ\Server\Loggers\Types\ServerMonitoringInfo;
use PHPMQ\Server\Monitors\ServerMonitor;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class ServerMonitorLogger
 * @package PHPMQ\Server\Loggers
 */
final class ServerMonitorLogger extends AbstractLogger
{
	/** @var MonitoringConfig */
	private $config;

	/** @var ServerMonitoringInfo */
	private $serverMonitoringInfo;

	/** @var ServerMonitor */
	private $serverMonitor;

	public function __construct(
		MonitoringConfig $config,
		ServerMonitor $serverMonitor,
		ServerMonitoringInfo $serverMonitoringInfo
	)
	{
		$this->config               = $config;
		$this->serverMonitoringInfo = $serverMonitoringInfo;
		$this->serverMonitor        = $serverMonitor;

		if ( $this->config->isEnabled() )
		{
			$this->serverMonitor->refresh( $this->serverMonitoringInfo );
		}
	}

	public function log( $level, $message, array $context = [] ) : void
	{
		if ( $this->config->isDisabled() )
		{
			return;
		}

		if ( $level !== LogLevel::DEBUG )
		{
			return;
		}

		if ( !isset( $context['monitoring'] ) )
		{
			return;
		}

		switch ( $context['monitoring'] )
		{
			case ServerMonitoring::CLIENT_CONNECTED:
				$this->serverMonitoringInfo->incrementConnectedClients();
				break;

			case ServerMonitoring::CLIENT_DISCONNECTED:
				$this->serverMonitoringInfo->decrementConnectedClients();
				break;

			case ServerMonitoring::MESSAGE_ENQUEUED:
				$this->serverMonitoringInfo->addMessage( $context['queueName'], $context['message'] );
				break;

			case ServerMonitoring::MESSAGE_DEQUEUED:
				$this->serverMonitoringInfo->removeMessage( $context['queueName'], $context['messageId'] );
				break;

			case ServerMonitoring::MESSAGE_DISPATCHED:
				$this->serverMonitoringInfo->markMessageAsDispatched( $context['queueName'], $context['messageId'] );
				break;

			case ServerMonitoring::MESSAGE_UNDISPATCHED:
				$this->serverMonitoringInfo->markMessageAsUndispatched( $context['queueName'], $context['messageId'] );
				break;

			case ServerMonitoring::QUEUE_FLUSHED:
				$this->serverMonitoringInfo->flushQueue( $context['queueName'] );
				break;

			case ServerMonitoring::ALL_QUEUES_FLUSHED:
				$this->serverMonitoringInfo->flushAllQueues();
				break;
		}

		$this->serverMonitor->refresh( $this->serverMonitoringInfo );
	}
}
