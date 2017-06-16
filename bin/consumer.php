<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server;

use PHPMQ\Server\Protocol\Constants\PacketLength;
use PHPMQ\Server\Protocol\Headers\MessageHeader;
use PHPMQ\Server\Protocol\Headers\PacketHeader;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

$socket = stream_socket_client( 'tcp://127.0.0.1:9100' );
stream_set_blocking( $socket, false );

$consumeRequest = new ConsumeRequest( new QueueName( 'Test-Queue' ), 2 );

fwrite( $socket, $consumeRequest->toString() );

echo "Sent consume request\n";

$messageBuilder = new MessageBuilder();

while ( true )
{
	usleep( 2000 );

	$reads  = [ $socket ];
	$writes = $excepts = null;

	stream_select( $reads, $writes, $excepts, 0 );

	if ( count( $reads ) === 0 )
	{
		continue;
	}

	$bytes = fread( $socket, PacketLength::MESSAGE_HEADER );

	if ( $bytes )
	{
		usleep( 300000 );

		echo "\n\n" . var_export( $bytes, true ) . "\n\n";

		$messageHeader = MessageHeader::fromString( $bytes );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$buffer = fread( $socket, PacketLength::PACKET_HEADER );

			$packetHeader = PacketHeader::fromString( $buffer );

			$buffer = fread( $socket, $packetHeader->getContentLength() );

			$packets[ $packetHeader->getPacketType() ] = $buffer;
		}

		/** @var MessageE2C $message */
		$message = $messageBuilder->buildMessage( $messageHeader, $packets );

		echo sprintf(
			'Received %s with ID %s from queue "%s" with content:',
			get_class( $message ),
			$message->getMessageId(),
			$message->getQueueName()
		);

		echo "\n{$message->toString()}\n";

		$acknowledgement = new Acknowledgement( $message->getQueueName(), $message->getMessageId() );

		fwrite( $socket, $acknowledgement->toString() );

		echo "\n√ Message acknowledged.\n--\n";
	}
	else
	{
		echo "Endpoint disconnected.\n";
		break;
	}
}

fclose( $socket );
