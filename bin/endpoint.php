<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\EventHandlers\MessageQueueClientConnectionEventHandler;
use PHPMQ\Server\EventHandlers\MessageQueueClientInboundEventHandler;
use PHPMQ\Server\EventHandlers\MessageQueueClientOutboundEventHandler;
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
	private const COLORS = [
		'<:fg>'        => "\e[39m",
		'<:bg>'        => "\e[49m",
		'<fg:red>'     => "\e[31m",
		'<fg:green>'   => "\e[32m",
		'<fg:yellow>'  => "\e[33m",
		'<fg:blue>'    => "\e[34m",
		'<fg:magenta>' => "\e[35m",
		'<fg:cyan>'    => "\e[36m",
		'<bg:red>'     => "\e[41m",
		'<bg:green>'   => "\e[42m",
		'<bg:yellow>'  => "\e[43m",
		'<bg:blue>'    => "\e[44m",
		'<bg:magenta>' => "\e[45m",
		'<bg:cyan>'    => "\e[46m",
	];

	public function log( $level, $message, array $context = [] )
	{
		printf(
			"[%s]: %s\n",
			$level,
			str_replace( array_keys( self::COLORS ), self::COLORS, $message )
		);
	}
};

$logger = new CompositeLogger();
$logger->addLoggers( $outputLogger );

$eventBus = new EventBus( $logger );
$storage  = new MessageQueueSQLite( $storageConfig );

$eventBus->addEventHandlers(
	new MessageQueueClientConnectionEventHandler( $storage ),
	new MessageQueueClientInboundEventHandler( $storage ),
	new MessageQueueClientOutboundEventHandler( $storage )
);

$messageQueueServerSocket = new ServerSocket( new NetworkSocket( '127.0.0.1', 9100 ) );
$adminServerSocket        = new ServerSocket( new NetworkSocket( '127.0.0.1', 9101 ) );

$endoint = new Endpoint( $eventBus, $logger );
$endoint->registerServers(
	new MessageQueueServer( $messageQueueServerSocket ),
	new AdminServer( $adminServerSocket )
);

$endoint->run();
