<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\EventListeners;

use PHPMQ\Server\Endpoint\Events\ClientHasConnectedEvent;
use PHPMQ\Server\Endpoint\Events\ClientHasDisconnectedEvent;
use PHPMQ\Server\Endpoint\Events\ClientMessageWasReceivedEvent;
use PHPMQ\Server\Monitors\ServerMonitor;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Types\MonitoringConfig;
use PHPMQ\Server\Types\ServerMonitoringInfo;

/**
 * Class ServerMonitoringEventListener
 * @package PHPMQ\Server\Endpoint\EventListeners
 */
final class ServerMonitoringEventListener extends AbstractEventListener
{
	/** @var ServerMonitor */
	private $monitor;

	/** @var MonitoringConfig */
	private $config;

	/** @var ServerMonitoringInfo */
	private $monitoringInfo;

	public function __construct( ServerMonitor $monitor, MonitoringConfig $config )
	{
		$this->monitor        = $monitor;
		$this->config         = $config;
		$this->monitoringInfo = new ServerMonitoringInfo();
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientHasConnectedEvent::class,
			ClientHasDisconnectedEvent::class,
			ClientMessageWasReceivedEvent::class,
		];
	}

	protected function whenClientHasConnected( ClientHasConnectedEvent $event ) : void
	{
		$this->monitoringInfo->incrementConnectedClients();

		$this->refreshMonitor();
	}

	private function refreshMonitor() : void
	{
		$this->monitor->refresh( $this->monitoringInfo );
	}

	protected function whenClientHasDisconnected( ClientHasDisconnectedEvent $event ) : void
	{
		$this->monitoringInfo->decrementConnectedClients();

		$this->refreshMonitor();
	}

	protected function whenClientMessageWasReceived( ClientMessageWasReceivedEvent $event ) : void
	{
		$client  = $event->getClient();
		$message = $event->getMessage();

		$messageType = $message->getMessageType()->getType();

		switch ( $messageType )
		{
			case MessageType::MESSAGE_C2E:
				/** @var MessageC2E $message */
				$this->monitoringInfo->addMessage( $message->getQueueName(), $message );
				break;

			case MessageType::CONSUME_REQUEST:
				/** @var ConsumeRequest $message */
				$this->handleConsumeRequest( $message, $client );
				break;

			case MessageType::ACKNOWLEDGEMENT:
				/** @var Acknowledgement $message */
				$this->handleAcknowledgement( $message, $client );
				break;
		}
	}
}
