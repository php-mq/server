<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Configs\ConfigBuilder;
use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\Endpoint\Loop;
use PHPMQ\Server\EventHandlers\Maintenance;
use PHPMQ\Server\EventHandlers\MessageQueue;
use PHPMQ\Server\Loggers\CompositeLogger;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Storage\Storage;
use PHPMQ\Server\StreamListeners\MaintenanceServerListener;
use PHPMQ\Server\StreamListeners\MessageQueueServerListener;
use PHPMQ\Server\Validators\ArgumentValidator;
use PHPMQ\Server\Validators\CompositeValidator;
use PHPMQ\Server\Validators\ConfigFileValidator;
use PHPMQ\Server\Validators\PHPVersionValidator;

$packageAutoloadScript = dirname( __DIR__ ) . '/vendor/autoload.php';
$pharAutoloadScript    = dirname( __DIR__, 4 ) . '/vendor/autoload.php';
$autoloadScript        = file_exists( $packageAutoloadScript ) ? $packageAutoloadScript : $pharAutoloadScript;

/** @noinspection PhpIncludeInspection */
require $autoloadScript;

$minPhpVersion     = '7.1.0';
$packageVersion    = '@package_version@';
$defaultConfigFile = dirname( __DIR__ ) . '/config/phpmq.default.xml';

$cliWriter = new CliWriter( $packageVersion );
$validator = new CompositeValidator();

$validator->addValidators(
	new PHPVersionValidator( $minPhpVersion, PHP_VERSION, PHP_BINARY, $packageVersion ),
	new ArgumentValidator( $argv ),
	new ConfigFileValidator( $argv[1] ?? $defaultConfigFile )
);

if ( $validator->failed() )
{
	foreach ( $validator->getMessages() as $message )
	{
		fwrite( STDERR, $cliWriter->writeLn( $message )->getOutput() );
	}

	exit( 1 );
}

try
{
	$configBuilder            = new ConfigBuilder( $argv[1] ?? $defaultConfigFile );
	$logger                   = CompositeLogger::fromConfigBuilder( $configBuilder );
	$storage                  = Storage::fromConfigBuilder( $configBuilder );
	$serverMonitoringInfo     = ServerMonitoringInfo::fromStorage( $storage );
	$messageQueueServerSocket = new ServerSocket( $configBuilder->getMessageQueueServerSocketAddress() );
	$maintenanceServerSocket  = new ServerSocket( $configBuilder->getMaintenanceServerSocketAddress() );
	$eventBus                 = new EventBus( $logger );
	$endpoint                 = new Endpoint( $logger, new Loop() );

	$consumptionPool = new ConsumptionPool();

	$eventBus->addEventHandlers(
		new MessageQueue\ClientConnectionEventHandler( $storage, $consumptionPool, $serverMonitoringInfo ),
		new MessageQueue\ClientInboundEventHandler( $storage, $consumptionPool, $serverMonitoringInfo ),
		new Maintenance\ClientConnectionEventHandler( $cliWriter ),
		new Maintenance\ClientInboundEventHandler( $storage, $cliWriter, $serverMonitoringInfo )
	);

	$endpoint->addServer( $messageQueueServerSocket, new MessageQueueServerListener( $eventBus ) );
	$endpoint->addServer( $maintenanceServerSocket, new MaintenanceServerListener( $eventBus ) );

	$endpoint->run();

	exit( 0 );
}
catch ( \Throwable $e )
{
	$cliWriter->clearScreen( 'FAILURE' )
			  ->writeLn( '<fg:red>ERROR:<:fg> ' . $e->getMessage() )
			  ->writeLn( 'Exception: ' . get_class( $e ) )
			  ->writeLn( 'In file %s on line %s', $e->getFile(), (string)$e->getLine() )
			  ->writeLn( $e->getTraceAsString() );

	fwrite( STDERR, $cliWriter->getOutput() );

	exit( 1 );
}
