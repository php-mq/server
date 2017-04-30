<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ;

use hollodotme\PHPMQ\Defaults\Configs\DefaultEndpointConfig;
use hollodotme\PHPMQ\Endpoint\Endpoint;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';

$config = new DefaultEndpointConfig();

$logger = new class extends AbstractLogger
{
	public function log( $level, $message, array $context = [] )
	{
		printf( "[%s]: %s\n", $level, sprintf( $message, ...$context ) );
	}
};

$endoint = new Endpoint( $config );
$endoint->setLogger( $logger );

$endoint->startListening();
