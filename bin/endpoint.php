<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Endpoint\Constants\SocketDomain;
use PHPMQ\Server\Endpoint\Constants\SocketType;
use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\Endpoint\Interfaces\ConfiguresEndpoint;
use PHPMQ\Server\Endpoint\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Endpoint\Types\UnixDomainSocket;
use PHPMQ\Server\MessageDispatchers\MessageDispatcher;
use PHPMQ\Server\MessageHandlers\AcknowledgementHandler;
use PHPMQ\Server\MessageHandlers\ConsumeRequestHandler;
use PHPMQ\Server\MessageHandlers\MessageC2EHandler;
use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueue;
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
		printf( "%s\n", sprintf( $message, ...$context ) );
	}
};

$storage    = new MessageQueueSQLite( $storageConfig );
$dispatcher = new MessageDispatcher( $storage );
$dispatcher->setLogger( $logger );

$endoint = new Endpoint( $endpointConfig, $dispatcher );
$endoint->setLogger( $logger );

$endoint->addMessageHandlers(
	new MessageC2EHandler( $storage ),
	new ConsumeRequestHandler(),
	new AcknowledgementHandler( $storage )
);

$endoint->startListening();
