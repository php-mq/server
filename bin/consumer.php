<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ;

use hollodotme\PHPMQ\Protocol\Constants\PacketLength;
use hollodotme\PHPMQ\Protocol\MessageHeader;
use hollodotme\PHPMQ\Protocol\Messages\Acknowledgement;
use hollodotme\PHPMQ\Protocol\Messages\ConsumeRequest;
use hollodotme\PHPMQ\Protocol\Messages\MessageBuilder;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Protocol\PacketHeader;
use hollodotme\PHPMQ\Types\QueueName;

require __DIR__ . '/../vendor/autoload.php';

$socket = socket_create( AF_UNIX, SOCK_STREAM, 0 );
socket_connect( $socket, '/tmp/phpmq.sock' );
socket_set_nonblock( $socket );

sleep( 1 );

$consumeRequest = new ConsumeRequest( new QueueName( 'Test-Queue' ), 1 );

socket_write( $socket, $consumeRequest->toString() );

echo "Sent consume request\n";

sleep( 1 );

$messageBuilder = new MessageBuilder();

while ( true )
{
	usleep( 2000 );

	$reads  = [ $socket ];
	$writes = $excepts = null;

	socket_select( $reads, $writes, $excepts, 0 );

	if ( count( $reads ) === 0 )
	{
		continue;
	}

	$buffer = '';
	$bytes  = socket_recv( $socket, $buffer, PacketLength::MESSAGE_HEADER, MSG_WAITALL );

	if ( $bytes !== false )
	{
		if ( null === $buffer )
		{
			echo "Endpoint disconnected.\n";
			break;
		}

		$messageHeader = MessageHeader::fromString( $buffer );
		$packetCount   = $messageHeader->getMessageType()->getPacketCount();

		$packets = [];

		for ( $i = 0; $i < $packetCount; $i++ )
		{
			$buffer = '';
			socket_recv( $socket, $buffer, PacketLength::PACKET_HEADER, MSG_WAITALL );

			$packetHeader = PacketHeader::fromString( $buffer );

			$buffer = '';
			socket_recv( $socket, $buffer, $packetHeader->getContentLength(), MSG_WAITALL );

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

		sleep( 1 );

		$acknowledgement = new Acknowledgement( $message->getQueueName(), $message->getMessageId() );

		socket_write( $socket, $acknowledgement->toString() );

		echo "\n√ Message acknowledged.\n--\n";
	}
}

socket_shutdown( $socket );
socket_close( $socket );
