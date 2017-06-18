<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Exceptions\ReadFailedException;
use PHPMQ\Server\Clients\Interfaces\CollectsClients;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Clients\MessageQueueClientPool;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Endpoint\Interfaces\ConfiguresEndpoint;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessages;
use PHPMQ\Server\Endpoint\Interfaces\ListensToClients;
use PHPMQ\Server\Endpoint\Sockets\ServerSocket;
use PHPMQ\Server\Loggers\Monitoring\ServerMonitor;
use PHPMQ\Server\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Endpoint
 * @package PHPMQ\Server\Endpoint
 */
final class Endpoint implements ListensToClients, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/** @var CollectsClients */
	private $clients;

	/** @var HandlesMessages */
	private $messageHandler;

	/** @var ServerMonitor */
	private $monitor;

	/** @var ServerSocket */
	private $messageQueueServerSocket;

	/** @var MessageQueueClientPool */
	private $messageQueueClients;

	/** @var ServerSocket */
	private $adminServerSocket;

	/** @var BuildsMessages */
	private $messageBuilder;

	public function __construct(
		ConfiguresEndpoint $config,
		CollectsClients $clientCollection,
		HandlesMessages $messageHandler,
		ServerMonitor $monitor
	)
	{
		$this->messageQueueServerSocket = new ServerSocket( $config->getMessageQueueServerAddress() );
		$this->adminServerSocket        = new ServerSocket( $config->getAdminServerAddress() );

		$this->clients        = $clientCollection;
		$this->messageHandler = $messageHandler;
		$this->monitor        = $monitor;
		$this->messageBuilder = new MessageBuilder();

		$this->setLogger( new NullLogger() );
	}

	public function setLogger( LoggerInterface $logger ) : void
	{
		$this->logger = $logger;

		$this->messageQueueServerSocket->setLogger( $logger );
		$this->adminServerSocket->setLogger( $logger );
		$this->clients->setLogger( $logger );
	}

	public function run() : void
	{
		$this->registerSignalHandler();

		$this->messageQueueServerSocket->startListening();
		$this->adminServerSocket->startListening();

		$this->loop();
	}

	private function registerSignalHandler() : void
	{
		if ( function_exists( 'pcntl_signal' ) )
		{
			pcntl_signal( SIGTERM, [ $this, 'shutDownBySignal' ] );
			pcntl_signal( SIGINT, [ $this, 'shutDownBySignal' ] );
		}
	}

	private function shutDownBySignal( int $signal ) : void
	{
		if ( in_array( $signal, [ SIGINT, SIGTERM, SIGKILL ], true ) )
		{
			$this->shutdown();
			exit( 0 );
		}
	}

	public function shutdown() : void
	{
		$this->logger->debug( 'Shutting endpoint down...' );

		$this->clients->shutDown();

		$this->messageQueueServerSocket->endListening();
		$this->adminServerSocket->endListening();

		$this->logger->debug( 'Good bye.' );
	}

	private function loop() : void
	{
		declare(ticks=1);

		while ( $this->socketsAreListening() )
		{
			usleep( 2000 );

			$this->messageQueueServerSocket->checkForNewClient( [ $this, 'handleNewMessageQueueClient' ] );
			$this->adminServerSocket->checkForNewClient( [ $this, 'handleNewAdminClient' ] );

//			$this->readMessagesFromActiveClients();
//
//			$this->clients->dispatchMessages();
//
//			$this->monitor->refresh();
		}
	}

	private function socketsAreListening() : bool
	{
		return ($this->messageQueueServerSocket->isListening() || $this->adminServerSocket->isListening());
	}

	public function handleNewMessageQueueClient( ServerSocket $socket, string $clientName, $clientSocket ) : void
	{
		$this->logger->debug(
			sprintf(
				'New message queue client %s connected to server socket %s.',
				$clientName,
				$socket->getName()
			)
		);

		$clientId = new ClientId( $clientName );
		$client   = new MessageQueueClient( $clientId, $clientSocket, $this->messageBuilder );

		$this->clients->add( $client );
	}

	public function handleNewAdminClient( ServerSocket $socket, string $clientName, $clientSocket ) : void
	{
		$this->logger->debug(
			sprintf(
				'New admin client %s connected to server socket %s.',
				$clientName,
				$socket->getName()
			)
		);
	}

	private function readMessagesFromActiveClients() : void
	{
		foreach ( $this->clients->getActive() as $client )
		{
			$messages = $this->readMessagesFromClient( $client );

			$this->handleMessagesFromClient( $client, $messages );
		}
	}

	private function readMessagesFromClient( MessageQueueClient $client ) : \Generator
	{
		do
		{
			$message = $this->readMessageFromClient( $client );

			if ( null === $message )
			{
				break;
			}

			yield $message;
		}
		while ( $client->hasUnreadData() );
	}

	private function readMessageFromClient( MessageQueueClient $client ) : ?CarriesInformation
	{
		try
		{
			return $client->readMessage();
		}
		catch ( ReadFailedException $e )
		{
			$this->logger->alert( $e->getMessage() );

			return null;
		}
		catch ( ClientDisconnectedException $e )
		{
			$this->clients->remove( $client );

			return null;
		}
	}

	public function handleMessagesFromClient( MessageQueueClient $client, iterable $messages ) : void
	{
		foreach ( $messages as $message )
		{
			$this->messageHandler->handle( $message, $client );
		}
	}

	public function __destruct()
	{
		$this->shutdown();
	}
}
