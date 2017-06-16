<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Monitoring;

use PHPMQ\Server\Loggers\Monitoring\Constants\ServerMonitoring;
use PHPMQ\Server\Loggers\Monitoring\Types\ServerMonitoringConfig;
use PHPMQ\Server\Loggers\Monitoring\Types\ServerMonitoringInfo;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class ServerMonitoringLogger
 * @package PHPMQ\Server\Loggers\Monitoring
 */
final class ServerMonitoringLogger extends AbstractLogger
{
	/** @var ServerMonitoringConfig */
	private $config;

	/** @var ServerMonitoringInfo */
	private $serverMonitoringInfo;

	public function __construct(
		ServerMonitoringConfig $config,
		ServerMonitoringInfo $serverMonitoringInfo
	)
	{
		$this->config               = $config;
		$this->serverMonitoringInfo = $serverMonitoringInfo;
	}

	public function log( $level, $message, array $context = [] ) : void
	{
		if ( $this->config->isDisabled() )
		{
			return;
		}

		if ( $level !== LogLevel::DEBUG || !isset( $context['monitoring'] ) )
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
	}
}
