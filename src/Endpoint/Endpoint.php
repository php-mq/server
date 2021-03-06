<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use Psr\Log\LoggerInterface;

/**
 * Class Endpoint
 * @package PHPMQ\Server\Endpoint
 */
final class Endpoint
{
	/** @var LoggerInterface */
	private $logger;

	/** @var Loop */
	private $loop;

	public function __construct( LoggerInterface $logger, TracksStreams $loop )
	{
		$this->logger = $logger;
		$this->loop   = $loop;
	}

	public function addServer( EstablishesStream $server, ListensForStreamActivity $handler ) : void
	{
		$handler->setLogger( $this->logger );

		$this->loop->addReadStream( $server->getStream(), $handler );
	}

	public function run() : void
	{
		$this->loop->start();
	}
}
