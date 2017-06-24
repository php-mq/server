<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Endpoint\Interfaces\ListensForActivity;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Monitoring\ServerMonitor;
use Psr\Log\LoggerInterface;

/**
 * Class Endpoint
 * @package PHPMQ\Server\Endpoint
 */
final class Endpoint
{
	/** @var array|ListensForActivity[] */
	private $servers;

	/** @var bool */
	private $isRunning;

	/** @var PublishesEvents */
	private $eventBus;

	/** @var LoggerInterface */
	private $logger;

	/** @var ServerMonitor */
	private $serverMonitor;

	public function __construct( PublishesEvents $eventBus, ServerMonitor $serverMonitor, LoggerInterface $logger )
	{
		$this->servers       = [];
		$this->isRunning     = false;
		$this->eventBus      = $eventBus;
		$this->serverMonitor = $serverMonitor;
		$this->logger        = $logger;
	}

	public function registerServers( ListensForActivity ...$servers ) : void
	{
		foreach ( $servers as $server )
		{
			$server->setLogger( $this->logger );

			$this->servers[] = $server;
		}
	}

	public function run() : void
	{
		$this->registerSignalHandler();

		foreach ( $this->servers as $server )
		{
			$server->start();
		}

		$this->isRunning = true;

		$this->loop();
	}

	private function registerSignalHandler() : void
	{
		if ( function_exists( 'pcntl_signal' ) )
		{
			pcntl_signal( SIGTERM, [ $this, 'shutDownBySignal' ] );
			pcntl_signal( SIGINT, [ $this, 'shutDownBySignal' ] );

			$this->logger->debug( 'Registered signal handler.' );
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
		$this->isRunning = false;

		foreach ( $this->servers as $server )
		{
			$server->stop();
		}

		$this->servers = [];
	}

	private function loop() : void
	{
		declare(ticks=1);

		while ( $this->isRunning )
		{
			usleep( 2000 );

			$this->handleServerEvents();

			$this->serverMonitor->refresh();
		}
	}

	private function handleServerEvents() : void
	{
		foreach ( $this->servers as $server )
		{
			$this->emitServerEvents( $server );
		}
	}

	private function emitServerEvents( ListensForActivity $server ) : void
	{
		foreach ( $server->getEvents() as $event )
		{
			if ( null !== $event )
			{
				$this->eventBus->publishEvent( $event );
			}
		}
	}

	public function __destruct()
	{
		if ( !empty( $this->servers ) )
		{
			$this->shutdown();
		}
	}
}
