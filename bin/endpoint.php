<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Clients\ClientCollection;
use PHPMQ\Server\Endpoint\Constants\SocketDomain;
use PHPMQ\Server\Endpoint\Constants\SocketType;
use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\Endpoint\EventBus;
use PHPMQ\Server\Endpoint\EventListeners\ClientConnectionEventListener;
use PHPMQ\Server\Endpoint\EventListeners\ClientMessageReceivedEventListener;
use PHPMQ\Server\Endpoint\Interfaces\ConfiguresEndpoint;
use PHPMQ\Server\Endpoint\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Endpoint\MessageHandler;
use PHPMQ\Server\Endpoint\Types\UnixDomainSocket;
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
	public function getSocketDomain() : int
	{
		return SocketDomain::UNIX;
	}

	public function getSocketType() : int
	{
		return SocketType::STREAM;
	}

	public function getSocketProtocol() : int
	{
		return 0;
	}

	public function getBindToAddress() : IdentifiesSocketAddress
	{
		return new UnixDomainSocket( '/tmp/phpmq.server.sock' );
	}

	public function getListenBacklog() : int
	{
		return SOMAXCONN;
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

$serverMonitor    = new ServerMonitor();
$monitoringConfig = ServerMonitoringConfig::fromCLIOptions();

$logger = new CompositeLogger();
$logger->addLoggers(
	new ServerMonitoringLogger( $monitoringConfig, $serverMonitor, new ServerMonitoringInfo() ),
	$outputLogger
);

$eventBus = new EventBus();
$eventBus->setLogger( $logger );

$storage = new MessageQueueSQLite( $storageConfig );
$storage->setLogger( $logger );

$dispatcher = new MessageDispatcher( $storage );
$dispatcher->setLogger( $logger );

$clientCollection = new ClientCollection( $dispatcher, $eventBus );
$clientCollection->setLogger( $logger );

$eventBus->addEventListeners(
	new ClientMessageReceivedEventListener( $storage ),
	new ClientConnectionEventListener( $storage )
);

$messageHandler = new MessageHandler( $eventBus );

$endoint = new Endpoint( $endpointConfig, $clientCollection, $messageHandler );
$endoint->setLogger( $logger );

$endoint->startListening();
