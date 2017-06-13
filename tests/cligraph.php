<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

require __DIR__ . '/../vendor/autoload.php';

use PHPMQ\Server\Loggers\Monitoring\Formatters\ByteFormatter;

$terminalWidth = exec( 'tput cols' );

function drawQueue( string $queuName, int $countMessages, int $queueSize, $rowColor )
{
	global $terminalWidth;

	printf(
		"{$rowColor}%-20s %5d %10s  %s\e[0m\r\n",
		$queuName,
		$countMessages,
		(new ByteFormatter( $queueSize ))->format( 0 ),
		str_repeat( '█', (int)(($terminalWidth - 39) * ($countMessages / 500)) )
	);
}

function clearScreen()
{
	echo "\e[2J\e[0;0H\r\n";
	echo "\e[30;42m PHP \e[37;41m MQ \e[30;42m - Monitor" . str_repeat( ' ', 27 ) . "\e[0m\r\n\n";
}

function drawGraph( int $queues )
{
	clearScreen();
	global $terminalWidth;

	$terminalWidth = exec( 'tput cols' );

	printf(
		"\e[44m Queues total:\e[43m %4d \e[44m  Clients connected:\e[43m %4d \e[0m\r\n\r\n",
		$queues,
		random_int( 1, 20 )
	);
	printf( "%-20s %5s %10s  Workload\r\n", 'Queue', 'Msgs', 'Size' );
	echo str_repeat( '=', 75 ) . "\r\n";

	for ( $i = 0; $i < $queues; $i++ )
	{
		$queue         = sprintf( 'Queue name %02d', $i + 1 );
		$countMessages = random_int( 1, 500 );

		drawQueue( $queue, $countMessages, (int)($countMessages * 256), $i % 2 === 0 ? "\e[0m" : "\e[2m" );
	}
}

while ( true )
{
	drawGraph( 20 );
	usleep( 500000 );
}
