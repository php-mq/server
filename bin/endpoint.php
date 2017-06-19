<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\EventHandlers\ClientConnectionEventHandler;
use PHPMQ\Server\EventHandlers\ClientMessageReceivedEventHandler;
use PHPMQ\Server\Loggers\CompositeLogger;
use PHPMQ\Server\Servers\AdminServer;
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
		printf( "[%s]: %s\n", $level, $message );
	}
};

$logger = new CompositeLogger();
$logger->addLoggers( $outputLogger );

$eventBus = new EventBus();
$eventBus->setLogger( $logger );

$storage = new MessageQueueSQLite( $storageConfig );
$storage->setLogger( $logger );

$eventBus->addEventHandlers(
	new ClientMessageReceivedEventHandler( $storage ),
	new ClientConnectionEventHandler( $storage )
);

$messageQueueServerSocket = new ServerSocket( new NetworkSocket( '127.0.0.1', 9100 ) );
$adminServerSocket        = new ServerSocket( new NetworkSocket( '127.0.0.1', 9101 ) );

$endoint = new Endpoint( $eventBus );
$endoint->registerServers(
	new MessageQueueServer( $messageQueueServerSocket ),
	new AdminServer( $adminServerSocket )
);

$endoint->run();
