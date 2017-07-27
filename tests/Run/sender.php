<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run;

use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Servers\Types\NetworkSocket;
use PHPMQ\Server\Tests\Run\Clients\ClientSocket;
use PHPMQ\Server\Tests\Run\Clients\Sender;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../../vendor/autoload.php';

$sender = new Sender(
	(new ClientSocket(
		new NetworkSocket( '127.0.0.1', 9100 )
	)
	)->getStream(),
	new MessageBuilder()
);

$fileContent = file_get_contents( __DIR__ . '/../Unit/Fixtures/test.jpg' );

$message1 = new MessageC2E( new QueueName( $argv[1] ), $fileContent );
$message2 = new MessageC2E( new QueueName( $argv[1] ), 'This is a second test' );

$messageId1 = $sender->writeMessage( $message1 );

echo "√ Sent message 'This is a first test', got message ID: {$messageId1}\n";

$messageId2 = $sender->writeMessage( $message2 );

echo "√ Sent message 'This is a second test', got message ID {$messageId2}\n";

$sender->disconnect();
