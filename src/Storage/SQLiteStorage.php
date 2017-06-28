<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage;

use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Storage\Exceptions\StorageException;
use PHPMQ\Server\Storage\Interfaces\ConfiguresSQLiteStorage;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;

/**
 * Class SQLiteStorage
 * @package PHPMQ\Server\Storage
 */
final class SQLiteStorage implements StoresMessages
{
	private const CREATE_TABLE_QUERY = 'BEGIN;
		 CREATE TABLE IF NOT EXISTS `queue` (
			`messageId` CHAR(32),
			`queueName` VARCHAR(50),
			`content` TEXT,
			`createdAt` INTEGER,
			`dispatched` INTEGER
		 );
		 CREATE UNIQUE INDEX IF NOT EXISTS messageIdQueueName ON `queue` (`messageId`, `queueName`);
		 COMMIT;';

	/** @var ConfiguresSQLiteStorage */
	private $config;

	/** @var \PDO */
	private $pdo;

	public function __construct( ConfiguresSQLiteStorage $config )
	{
		$this->config = $config;
	}

	/**
	 * @param IdentifiesQueue     $queueName
	 * @param ProvidesMessageData $message
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function enqueue( IdentifiesQueue $queueName, ProvidesMessageData $message ) : void
	{
		$this->execTransactional(
			function () use ( $queueName, $message )
			{
				$statement = $this->getPDO()->prepare(
					'INSERT INTO `queue` 
					(`messageId`, `queueName`, `content`, `createdAt`, `dispatched`) 
				 VALUES 
				    (:messageId, :queueName, :content, :createdAt, 0)'
				);

				$statement->execute(
					[
						'messageId' => $message->getMessageId()->toString(),
						'queueName' => $queueName->toString(),
						'content'   => $message->getContent(),
						'createdAt' => $message->createdAt(),
					]
				);
			},
			'enqueue'
		);
	}

	private function execTransactional( \Closure $operation, string $method ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$operation->call( $this );

			$this->getPDO()->commit();
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw StorageException::fromMethodFailure( $method, $e );
		}
	}

	private function getPDO() : \PDO
	{
		if ( null === $this->pdo )
		{
			$this->config->getStoragePath();

			$this->pdo = new \PDO( 'sqlite:' . $this->config->getStoragePath() );
			$this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

			$this->pdo->exec( self::CREATE_TABLE_QUERY );
		}

		return $this->pdo;
	}

	/**
	 * @param IdentifiesQueue   $queueName
	 * @param IdentifiesMessage $messageId
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function dequeue( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->execTransactional(
			function () use ( $queueName, $messageId )
			{
				$statement = $this->getPDO()->prepare(
					'DELETE FROM `queue` 
                    WHERE `queueName` = :queueName
                    AND `messageId` = :messageId'
				);

				$statement->execute(
					[
						'queueName' => $queueName->toString(),
						'messageId' => $messageId->toString(),
					]
				);
			},
			'dequeue'
		);
	}

	/**
	 * @param IdentifiesQueue   $queueName
	 * @param IdentifiesMessage $messageId
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function markAsDispached( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->execTransactional(
			function () use ( $queueName, $messageId )
			{
				$statement = $this->getPDO()->prepare(
					'UPDATE `queue` SET `dispatched` = 1 
				    WHERE `queueName` = :queueName 
				    AND `messageId` = :messageId'
				);

				$statement->execute(
					[
						'queueName' => $queueName->toString(),
						'messageId' => $messageId->toString(),
					]
				);
			},
			'markAsDispatched'
		);
	}

	/**
	 * @param IdentifiesQueue   $queueName
	 * @param IdentifiesMessage $messageId
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function markAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->execTransactional(
			function () use ( $queueName, $messageId )
			{
				$statement = $this->getPDO()->prepare(
					'UPDATE `queue` SET `dispatched` = 0 
				 WHERE `queueName` = :queueName 
				    AND `messageId` = :messageId'
				);

				$statement->execute(
					[
						'queueName' => $queueName->toString(),
						'messageId' => $messageId->toString(),
					]
				);
			},
			'markAsUndispatched'
		);
	}

	/**
	 * @param IdentifiesQueue $queueName
	 * @param int             $countMessages
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 * @return \Generator|ProvidesMessageData[]
	 */
	public function getUndispatched( IdentifiesQueue $queueName, int $countMessages = 1 ) : \Generator
	{
		try
		{
			$statement = $this->getPDO()->prepare(
				"SELECT `messageId`, `content`, `createdAt`
				 FROM `queue` 
				 WHERE `queueName` = :queueName
				    AND `dispatched` = 0 
				 ORDER BY `createdAt` ASC 
				 LIMIT {$countMessages}"
			);

			$statement->execute( [ 'queueName' => $queueName->toString() ] );

			while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
			{
				yield new Message( new MessageId( $row['messageId'] ), $row['content'], (int)$row['createdAt'] );
			}
		}
		catch ( \PDOException $e )
		{
			throw StorageException::fromMethodFailure( 'getUndispatched', $e );
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function flushQueue( IdentifiesQueue $queueName ) : void
	{
		$this->execTransactional(
			function () use ( $queueName )
			{
				$statment = $this->getPDO()->prepare( 'DELETE FROM `queue` WHERE `queueName` = :queueName' );
				$statment->execute( [ 'queueName' => $queueName->toString() ] );
			},
			'flushQueue'
		);
	}

	/**
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function flushAllQueues() : void
	{
		try
		{
			$this->getPDO()->exec( 'DROP TABLE IF EXISTS `queue`' );
			$this->getPDO()->exec( self::CREATE_TABLE_QUERY );
		}
		catch ( \PDOException $e )
		{
			throw StorageException::fromMethodFailure( 'flushAllQueues', $e );
		}
	}

	public function getAllUndispatchedGroupedByQueueName() : \Generator
	{
		$statement = $this->getPDO()->query(
			'SELECT queueName, COUNT(1) AS countMessages 
			 FROM `queue` WHERE 1 GROUP BY queueName'
		);

		while ( $row = $statement->fetchObject() )
		{
			$queueName = new QueueName( $row->queueName );

			yield $queueName => $this->getUndispatched( $queueName, (int)$row->countMessages );
		}
	}

	public function resetAllDispatched() : void
	{
		$this->execTransactional(
			function ()
			{
				$this->getPDO()->exec( 'UPDATE `queue` SET `dispatched` = 0 WHERE 1' );
			},
			'resetAllDispatched'
		);
	}
}
