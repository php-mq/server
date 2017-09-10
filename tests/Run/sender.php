<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run;

use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Server\Tests\Run\Clients\ClientSocket;
use PHPMQ\Server\Tests\Run\Clients\Sender;
use PHPMQ\Server\Tests\Run\Clients\TlsSocket;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../../vendor/autoload.php';

$sender = new Sender(
	(new ClientSocket(
		new TlsSocket(
			'127.0.0.1', 9443,
			[
				'ssl' => [
					'local_cert'        => __DIR__ . '/../../tests/TLS/server.pem',
					'passphrase'        => 'root',
					'allow_self_signed' => true,
					'verify_peer'       => true,
					'verify_peer_name'  => true,
					'peer_name'         => 'phpmq.org',
				],
			]
		)
	))->getStream()
);

$fileContent = file_get_contents( __DIR__ . '/../Unit/Fixtures/test.jpg' );

$message1 = new MessageClientToServer( new QueueName( $argv[1] ), $fileContent );
$message2 = new MessageClientToServer( new QueueName( $argv[1] ), 'This is a second test' );

$sender->writeMessage( $message1 );

echo "√ Sent message 'This is a first test'\n";

$sender->writeMessage( $message2 );

echo "√ Sent message 'This is a second test'\n";

$sender->disconnect();
