<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ;

require __DIR__ . '/../vendor/autoload.php';

$socket = fsockopen( 'unix:///tmp/phpmq.sock', -1 );

sleep( 1 );

fwrite( $socket, 'Woot' );

sleep( 1 );

fwrite( $socket, 'Woot' );

sleep( 1 );

fclose( $socket );
