<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Run;

use PHPMQ\Protocol\Constants\PacketLength;
use PHPMQ\Protocol\Messages\Acknowledgement;
use PHPMQ\Protocol\Messages\ConsumeRequest;
use PHPMQ\Protocol\Messages\MessageServerToClient;
use PHPMQ\Protocol\Types\MessageHeader;
use PHPMQ\Protocol\Types\PacketHeader;
use PHPMQ\Server\Tests\Run\Clients\ClientSocket;
use PHPMQ\Server\Tests\Run\Clients\MessageBuilder;
use PHPMQ\Server\Tests\Run\Clients\TlsSocket;
use PHPMQ\Server\Types\QueueName;
use PHPMQ\Stream\Constants\ChunkSize;

require __DIR__ . '/../../vendor/autoload.php';

$consumer = (new ClientSocket(
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
))->getStream();

$consumeRequest = new ConsumeRequest( new QueueName( $argv[1] ), 5 );

$consumer->writeChunked( $consumeRequest->toString(), ChunkSize::WRITE );

echo "Sent consume request\n";

sleep( 1 );

$messageBuilder = new MessageBuilder();

while ( true )
{
	$reads = [];
	$consumer->collectRawStream( $reads );
	$writes = $excepts = null;

	usleep( 200000 );
	
	if ( !@stream_select( $reads, $writes, $excepts, 0, 200000 ) )
	{
		continue;
	}

	do
	{
		$bytes = $consumer->read( PacketLength::MESSAGE_HEADER );

		if ( empty( $bytes ) )
		{
			echo "Endpoint disconnected.\n";
			break 2;
		}

		$messageHeader = MessageHeader::fromString( $bytes );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$buffer       = $consumer->readChunked( PacketLength::PACKET_HEADER, ChunkSize::READ );
			$packetHeader = PacketHeader::fromString( $buffer );

			$buffer = $consumer->readChunked( $packetHeader->getContentLength(), ChunkSize::READ );

			$packets[ $packetHeader->getPacketType() ] = $buffer;
		}

		/** @var MessageServerToClient $message */
		$message = $messageBuilder->buildMessage( $messageHeader, $packets );

		echo sprintf(
			'Received %s with ID %s from queue "%s" with content:',
			get_class( $message ),
			$message->getMessageId(),
			$message->getQueueName()
		);

		usleep( 30000 );

		$acknowledgement = new Acknowledgement( $message->getQueueName(), $message->getMessageId() );

		$consumer->writeChunked( $acknowledgement->toString(), ChunkSize::WRITE );

		echo "\n√ Message acknowledged.\n--\n";
	}
	while ( $consumer->hasUnreadBytes() );
}

$consumer->shutDown();
$consumer->close();
