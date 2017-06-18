<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Clients\ClientCollection;
use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\Endpoint\EventBus;
use PHPMQ\Server\Endpoint\EventListeners\ClientConnectionEventListener;
use PHPMQ\Server\Endpoint\EventListeners\ClientMessageReceivedEventListener;
use PHPMQ\Server\Endpoint\Interfaces\ConfiguresEndpoint;
use PHPMQ\Server\Endpoint\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Endpoint\MessageQueueMessageHandler;
use PHPMQ\Server\Endpoint\Types\NetworkSocket;
use PHPMQ\Server\Loggers\CompositeLogger;
use PHPMQ\Server\Loggers\Monitoring\ServerMonitor;
use PHPMQ\Server\Loggers\Monitoring\ServerMonitoringLogger;
use PHPMQ\Server\Loggers\Monitoring\Types\ServerMonitoringConfig;
use PHPMQ\Server\Loggers\Monitoring\Types\ServerMonitoringInfo;
use PHPMQ\Server\MessageDispatchers\MessageDispatcher;
use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueueSQLite;
use PHPMQ\Server\Storage\MessageQueueSQLite;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';

$endpointConfig = new class implements ConfiguresEndpoint
{
	public function getMessageQueueServerAddress() : IdentifiesSocketAddress
	{
		return new NetworkSocket( '127.0.0.1', 9100 );
	}

	public function getAdminServerAddress() : IdentifiesSocketAddress
	{
		return new NetworkSocket( '127.0.0.1', 9101 );
	}
};

$storageConfig = new class implements ConfiguresMessageQueueSQLite
{
	public function getMessageQueuePath() : string
	{
		return ':memory:';
	}
};

$outputLogger = new class extends AbstractLogger
{
	public function log( $level, $message, array $context = [] )
	{
		printf( "[%s]: %s\n", $level, $message );
	}
};

$monitoringConfig = ServerMonitoringConfig::fromCLIOptions();
$monitoringInfo   = new ServerMonitoringInfo();
$monitor          = new ServerMonitor( $monitoringConfig, $monitoringInfo );

$logger = new CompositeLogger();
$logger->addLoggers( new ServerMonitoringLogger( $monitoringConfig, $monitoringInfo ), $outputLogger );

$eventBus = new EventBus();
$eventBus->setLogger( $logger );

$storage = new MessageQueueSQLite( $storageConfig );
$storage->setLogger( $logger );

$dispatcher = new MessageDispatcher( $storage );
$dispatcher->setLogger( $logger );

$clientCollection = new ClientCollection( $dispatcher, $eventBus );

$eventBus->addEventListeners(
	new ClientMessageReceivedEventListener( $storage ),
	new ClientConnectionEventListener( $storage )
);

$messageHandler = new MessageQueueMessageHandler( $eventBus );

$endoint = new Endpoint( $endpointConfig, $clientCollection, $messageHandler, $monitor );
$endoint->setLogger( $logger );

$endoint->run();
