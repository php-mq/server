<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ;

use hollodotme\PHPMQ\Endpoint\Constants\SocketDomain;
use hollodotme\PHPMQ\Endpoint\Constants\SocketType;
use hollodotme\PHPMQ\Endpoint\Endpoint;
use hollodotme\PHPMQ\Endpoint\Interfaces\ConfiguresEndpoint;
use hollodotme\PHPMQ\Endpoint\Interfaces\IdentifiesSocketAddress;
use hollodotme\PHPMQ\Endpoint\Types\UnixDomainSocket;
use hollodotme\PHPMQ\MessageDispatchers\MessageDispatcher;
use hollodotme\PHPMQ\MessageHandlers\AcknowledgementHandler;
use hollodotme\PHPMQ\MessageHandlers\ConsumeRequestHandler;
use hollodotme\PHPMQ\MessageHandlers\MessageC2EHandler;
use hollodotme\PHPMQ\Storage\Interfaces\ConfiguresMessageQueue;
use hollodotme\PHPMQ\Storage\MessageQueueSQLite;
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
		return new UnixDomainSocket( '/tmp/phpmq.sock' );
	}

	public function getListenBacklog() : int
	{
		return SOMAXCONN;
	}
};

$storageConfig = new class implements ConfiguresMessageQueue
{
	public function getMessageQueuePath() : string
	{
		return ':memory:';
	}
};

$logger = new class extends AbstractLogger
{
	public function log( $level, $message, array $context = [] )
	{
		printf( "[%s]: %s\n", $level, sprintf( $message, ...$context ) );
	}
};

$storage    = new MessageQueueSQLite( $storageConfig );
$dispatcher = new MessageDispatcher( $storage );

$endoint = new Endpoint( $endpointConfig, $dispatcher );
$endoint->setLogger( $logger );

$endoint->addMessageHandlers(
	new MessageC2EHandler( $storage ),
	new ConsumeRequestHandler(),
	new AcknowledgementHandler( $storage )
);

$endoint->startListening();
