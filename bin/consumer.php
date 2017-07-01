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

function fread_stream( $fp, $length )
{
	$buffer = '';

	while ( $length > 0 )
	{
		$chunkSize = (int)min( $length, 1024 );
		$buffer    .= (string)fread( $fp, $chunkSize );
		$length    -= $chunkSize;
	}

	return $buffer;
}

function fwrite_stream( $fp, $string )
{
	for ( $written = 0, $writtenMax = strlen( $string ); $written < $writtenMax; $written += $fwrite )
	{
		$fwrite = fwrite( $fp, substr( $string, $written ) );
		if ( $fwrite === false )
		{
			return $written;
		}
	}

	return $written;
}

$socket = stream_socket_client( 'tcp://127.0.0.1:9100' );
stream_set_blocking( $socket, false );

$socketName = stream_socket_get_name( $socket, true );
echo 'Connected to server: ' . $socketName . "\n";

$consumeRequest = new ConsumeRequest( new QueueName( $argv[1] ), 5 );

fwrite( $socket, $consumeRequest->toString() );

echo "Sent consume request\n";

sleep( 1 );

$messageBuilder = new MessageBuilder();

while ( true )
{
	$reads  = [ $socket ];
	$writes = $excepts = null;

	if ( !@stream_select( $reads, $writes, $excepts, 0 ) )
	{
		usleep( 20000 );
		continue;
	}

	do
	{
		$bytes = fread_stream( $socket, PacketLength::MESSAGE_HEADER );

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
			$buffer = fread_stream( $socket, PacketLength::PACKET_HEADER );

			$packetHeader = PacketHeader::fromString( $buffer );

			$buffer = fread_stream( $socket, $packetHeader->getContentLength() );

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

		usleep( 300000 );

		$acknowledgement = new Acknowledgement( $message->getQueueName(), $message->getMessageId() );

		fwrite_stream( $socket, $acknowledgement->toString() );

		echo "\n√ Message acknowledged.\n--\n";

		$metaData = stream_get_meta_data( $socket );
	}
	while ( $metaData['unread_bytes'] > 0 );
}

fclose( $socket );
