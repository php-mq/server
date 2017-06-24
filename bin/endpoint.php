<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Constants\AnsiColors;
use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\EventHandlers\Maintenance;
use PHPMQ\Server\EventHandlers\MessageQueue;
use PHPMQ\Server\Loggers\CompositeLogger;
use PHPMQ\Server\Monitoring\ServerMonitor;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Servers\MaintenanceServer;
use PHPMQ\Server\Servers\MessageQueueServer;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Servers\Types\NetworkSocket;
use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueueSQLite;
use PHPMQ\Server\Storage\MessageQueueSQLite;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';

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
		printf(
			"[%s]: %s\n",
			$level,
			str_replace( array_keys( AnsiColors::COLORS ), AnsiColors::COLORS, $message )
		);
	}
};

$logger = new CompositeLogger();
$logger->addLoggers( $outputLogger );

$eventBus = new EventBus( $logger );
$storage  = new MessageQueueSQLite( $storageConfig );

$cliWriter            = new CliWriter();
$serverMonitoringInfo = new ServerMonitoringInfo();

$eventBus->addEventHandlers(
	new MessageQueue\ClientConnectionEventHandler( $storage, $serverMonitoringInfo ),
	new MessageQueue\ClientInboundEventHandler( $storage, $serverMonitoringInfo ),
	new MessageQueue\ClientOutboundEventHandler( $storage, $serverMonitoringInfo ),
	new Maintenance\ClientConnectionEventHandler( $cliWriter ),
	new Maintenance\ClientInboundEventHandler( $cliWriter, $serverMonitoringInfo )
);

$messageQueueServerSocket = new ServerSocket( new NetworkSocket( '127.0.0.1', 9100 ) );
$maintenanceServerSocket  = new ServerSocket( new NetworkSocket( '127.0.0.1', 9101 ) );

$serverMonitor = new ServerMonitor( $serverMonitoringInfo, $cliWriter );

$endoint = new Endpoint( $eventBus, $serverMonitor, $logger );
$endoint->registerServers(
	new MessageQueueServer( $messageQueueServerSocket ),
	new MaintenanceServer( $maintenanceServerSocket )
);

$endoint->run();
