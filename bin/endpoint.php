<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Configs\LogFileLoggerConfig;
use PHPMQ\Server\Constants\AnsiColors;
use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\EventHandlers\Maintenance;
use PHPMQ\Server\EventHandlers\MessageQueue;
use PHPMQ\Server\Loggers\CompositeLogger;
use PHPMQ\Server\Loggers\Constants\LogLevel;
use PHPMQ\Server\Loggers\LogFileLogger;
use PHPMQ\Server\Monitoring\ServerMonitor;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Servers\MaintenanceServer;
use PHPMQ\Server\Servers\MessageQueueServer;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Servers\Types\NetworkSocket;
use PHPMQ\Server\Storage\Interfaces\ConfiguresSQLiteStorage;
use PHPMQ\Server\Storage\SQLiteStorage;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';

$storageConfigSQLite = new class implements ConfiguresSQLiteStorage
{
	public function getStoragePath() : string
	{
//		return '/tmp/phpmq.sqlite';
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

$logFileLogger = new LogFileLogger(
	new LogFileLoggerConfig(
		dirname( __DIR__ ) . '/build/logs/phpmq.log',
		LogLevel::LOG_LEVEL_DEBUG
	)
);

$logger = new CompositeLogger();
$logger->addLoggers( $outputLogger, $logFileLogger );

$eventBus = new EventBus( $logger );
$storage  = new SQLiteStorage( $storageConfigSQLite );

$cliWriter            = new CliWriter();
$serverMonitoringInfo = ServerMonitoringInfo::fromStorage( $storage );

$eventBus->addEventHandlers(
	new MessageQueue\ClientConnectionEventHandler( $storage, $serverMonitoringInfo ),
	new MessageQueue\ClientInboundEventHandler( $storage, $serverMonitoringInfo ),
	new MessageQueue\ClientOutboundEventHandler( $storage, $serverMonitoringInfo ),
	new Maintenance\ClientConnectionEventHandler( $cliWriter ),
	new Maintenance\ClientInboundEventHandler( $storage, $cliWriter, $serverMonitoringInfo )
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
