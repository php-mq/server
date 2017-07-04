<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Clients\ClientSocket;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Servers\Types\NetworkSocket;
use PHPMQ\Server\Streams\Constants\ChunkSize;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

$sender = (new ClientSocket( new NetworkSocket( '127.0.0.1', 9100 ) ))->getStream();

$fileContent = file_get_contents( __DIR__ . '/../tests/Unit/Fixtures/test.jpg' );

$message1 = new MessageC2E( new QueueName( $argv[1] ), $fileContent );
$message2 = new MessageC2E( new QueueName( $argv[1] ), 'This is a second test' );

$sender->writeChunked( $message1->toString(), ChunkSize::WRITE );

echo "√ Sent message 'This is a first test'\n";

$sender->writeChunked( $message2->toString(), ChunkSize::WRITE );

echo "√ Sent message 'This is a second test'\n";

$sender->shutDown();
$sender->close();
