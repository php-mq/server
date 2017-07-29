<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run;

use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Server\Servers\Types\NetworkSocket;
use PHPMQ\Server\Tests\Run\Clients\ClientSocket;
use PHPMQ\Server\Tests\Run\Clients\Sender;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../../vendor/autoload.php';

$sender = new Sender(
	(new ClientSocket(
		new NetworkSocket( '127.0.0.1', 9100 )
	)
	)->getStream()
);

$fileContent = file_get_contents( __DIR__ . '/../Unit/Fixtures/test.jpg' );

$message1 = new MessageClientToServer( new QueueName( $argv[1] ), $fileContent );
$message2 = new MessageClientToServer( new QueueName( $argv[1] ), 'This is a second test' );

$sender->writeMessage( $message1 );

echo "√ Sent message 'This is a first test'\n";

$sender->writeMessage( $message2 );

echo "√ Sent message 'This is a second test'\n";

$sender->disconnect();
