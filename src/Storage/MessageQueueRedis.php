<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage;

use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueueRedis;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;

/**
 * Class MessageQueueRedis
 * @package PHPMQ\Server\Storage
 */
final class MessageQueueRedis implements StoresMessages
{
	private const PREFIX_DEFAULT         = 'PHPMQ:';

	public const  BGSAVE_DEFAULT         = 0;

	public const  BGSAVE_ALWAYS          = 1;

	public const  BGSAVE_ENQUEUE_DEQUEUE = 2;

	private const KEY_QUEUE_SET          = 'queues';

	/** @var ConfiguresMessageQueueRedis */
	private $config;

	/** @var \Redis */
	private $redis;

	public function __construct( ConfiguresMessageQueueRedis $config )
	{
		$this->config = $config;
	}

	public function enqueue( IdentifiesQueue $queueName, ProvidesMessageData $message ) : void
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->getRedis()->multi()
		     ->sAdd( self::KEY_QUEUE_SET, $queueName->toString() )
		     ->rPush(
			     $this->getUndispatchedQueueKey( $queueName ),
			     $message->getMessageId()->toString()
		     )->hMset(
				$this->getMessageKey( $queueName, $message->getMessageId() ),
				[
					'messageId' => $message->getMessageId()->toString(),
					'content'   => $message->getContent(),
					'createdAt' => $message->createdAt(),
				]
			)->exec();

		$this->bgSave( 'enqueue' );
	}

	private function bgSave( string $methodName ) : void
	{
		switch ( $this->config->getBackgroundSaveBehaviour() )
		{
			case self::BGSAVE_DEFAULT:
				break;

			case self::BGSAVE_ALWAYS:
				$this->getRedis()->bgsave();
				break;

			case self::BGSAVE_ENQUEUE_DEQUEUE:
				if ( in_array( $methodName, [ 'enqueue', 'dequeue', 'resetAllDispatched' ], true ) )
				{
					$this->getRedis()->bgsave();
				}
				break;
		}
	}

	private function getRedis() : \Redis
	{
		if ( null === $this->redis )
		{
			$this->redis = new \Redis();

			$connectResult = $this->redis->connect(
				$this->config->getHost(),
				$this->config->getPort(),
				$this->config->getTimeout()
			);

			$this->guardCouldConnectToRedis( $connectResult );

			if ( null !== $this->config->getPassword() )
			{
				$authResult = $this->redis->auth( $this->config->getPassword() );
				$this->guardConnectionIsAuthenticated( $authResult );
			}

			$this->redis->setOption( \Redis::OPT_PREFIX, $this->config->getPrefix() ?? self::PREFIX_DEFAULT );
			$this->redis->setOption( \Redis::OPT_SERIALIZER, (string)\Redis::SERIALIZER_NONE );
		}

		return $this->redis;
	}

	private function guardCouldConnectToRedis( bool $connectResult ) : void
	{
		if ( false === $connectResult )
		{
			throw new RuntimeException( 'Could not connect to redis server.' );
		}
	}

	private function getMessageKey( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : string
	{
		return sprintf( 'message:%s:%s', $queueName->toString(), $messageId->toString() );
	}

	private function guardConnectionIsAuthenticated( bool $authResult ) : void
	{
		if ( false === $authResult )
		{
			throw new RuntimeException( 'Redis connection authentication failed.' );
		}
	}

	private function getUndispatchedQueueKey( IdentifiesQueue $queueName ) : string
	{
		return sprintf( 'queue:%s:undispatched', $queueName->toString() );
	}

	private function getDispatchedQueueKey( IdentifiesQueue $queueName ) : string
	{
		return sprintf( 'queue:%s:dispatched', $queueName->toString() );
	}

	public function dequeue( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->getRedis()->multi()
		     ->lRem( $this->getDispatchedQueueKey( $queueName ), $messageId->toString(), 1 )
		     ->lRem( $this->getUndispatchedQueueKey( $queueName ), $messageId->toString(), 1 )
		     ->del( $this->getMessageKey( $queueName, $messageId ) )
		     ->exec();

		/** @noinspection PhpUndefinedMethodInspection */
		$queueListLength = array_sum(
			$this->getRedis()->multi()
			     ->lLen( $this->getDispatchedQueueKey( $queueName ) )
			     ->lLen( $this->getUndispatchedQueueKey( $queueName ) )
			     ->exec()
		);

		if ( 0 === (int)$queueListLength )
		{
			$this->getRedis()->sRem( self::KEY_QUEUE_SET, $queueName->toString() );
		}

		$this->bgSave( 'dequeue' );
	}

	public function markAsDispached( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->getRedis()->multi()
		     ->lRem( $this->getUndispatchedQueueKey( $queueName ), $messageId->toString(), 1 )
		     ->rPush( $this->getDispatchedQueueKey( $queueName ), $messageId->toString() )
		     ->exec();

		$this->bgSave( 'markAsDispatched' );
	}

	public function markAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->getRedis()->multi()
		     ->lRem( $this->getDispatchedQueueKey( $queueName ), $messageId->toString(), 1 )
		     ->rPush( $this->getUndispatchedQueueKey( $queueName ), $messageId->toString() )
		     ->exec();

		$this->bgSave( 'markAsUndispatched' );
	}

	public function getUndispatched( IdentifiesQueue $queueName, int $countMessages = 1 ) : \Generator
	{
		$messageIds = $this->getRedis()->lRange( $this->getUndispatchedQueueKey( $queueName ), 0, $countMessages - 1 );

		if ( !is_array( $messageIds ) || 0 === count( $messageIds ) )
		{
			return;
		}

		yield from $this->createMessagesFromQueue( $queueName, $messageIds );
	}

	/**
	 * @param IdentifiesQueue $queueName
	 * @param array           $messageIds
	 *
	 * @return \Generator|ProvidesMessageData[]
	 */
	private function createMessagesFromQueue( IdentifiesQueue $queueName, array $messageIds ) : \Generator
	{
		$pipe = $this->getRedis()->multi();

		foreach ( $messageIds as $msgId )
		{
			$messageId = new MessageId( $msgId );
			$pipe->hGetAll( $this->getMessageKey( $queueName, $messageId ) );
		}

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$messages = (array)$pipe->exec();

		foreach ( $messages as $message )
		{
			yield new Message(
				new MessageId( $message['messageId'] ),
				(string)$message['content'],
				(int)$message['createdAt']
			);
		}
	}

	public function flushQueue( IdentifiesQueue $queueName ) : void
	{
		$undispatchedQueueKey = $this->getUndispatchedQueueKey( $queueName );
		$dispatchedQueueKey   = $this->getDispatchedQueueKey( $queueName );

		$messageIds = array_merge(
			$this->getRedis()->lRange( $undispatchedQueueKey, 0, -1 ),
			$this->getRedis()->lRange( $dispatchedQueueKey, 0, -1 )
		);

		$messageIds = array_map(
			function ( string $messageId ) use ( $queueName )
			{
				return $this->getMessageKey( $queueName, new MessageId( $messageId ) );
			},
			$messageIds
		);

		/** @noinspection PhpUndefinedMethodInspection */
		$this->getRedis()->multi()
		     ->sRem( self::KEY_QUEUE_SET, $queueName->toString() )
		     ->del( $undispatchedQueueKey, $dispatchedQueueKey, ...$messageIds );

		$this->bgSave( 'flushQueue' );
	}

	public function flushAllQueues() : void
	{
		$this->getRedis()->flushDB();

		$this->bgSave( 'flushAllQueues' );
	}

	private function getAllQueueNames() : array
	{
		return array_map(
			function ( $queueName )
			{
				return new QueueName( $queueName );
			},
			$this->getRedis()->sMembers( self::KEY_QUEUE_SET )
		);
	}

	public function resetAllDispatched() : void
	{
		foreach ( $this->getAllQueueNames() as $queueName )
		{
			$this->markMessagesInQueueAsUndispatched( $queueName );
		}

		$this->bgSave( 'resetAllDispatched' );
	}

	private function markMessagesInQueueAsUndispatched( IdentifiesQueue $queueName ) : void
	{
		$queueKey    = $this->getDispatchedQueueKey( $queueName );
		$queueLength = (int)$this->getRedis()->lLen( $queueKey );

		if ( 0 === $queueLength )
		{
			return;
		}

		$messageIds = $this->getRedis()->lRange( $queueKey, 0, $queueLength - 1 );

		foreach ( $messageIds as $messageId )
		{
			$this->markAsUndispatched( $queueName, new MessageId( $messageId ) );
		}
	}

	/**
	 * @return \Generator|ProvidesMessageData[][]
	 */
	public function getAllUndispatchedGroupedByQueueName() : \Generator
	{
		$redis = $this->getRedis();

		foreach ( $this->getAllQueueNames() as $queueName )
		{
			$queueKey    = $this->getUndispatchedQueueKey( $queueName );
			$queueLength = (int)$redis->lLen( $queueKey );

			if ( 0 === $queueLength )
			{
				continue;
			}

			yield $queueName => $this->getUndispatched( $queueName, $queueLength );
		}
	}
}
