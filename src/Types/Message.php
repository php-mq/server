<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Types;

use PHPMQ\Protocol\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;

/**
 * Class Message
 * @package PHPMQ\Server\Types
 */
final class Message implements ProvidesMessageData
{
	/** @var IdentifiesMessage */
	private $messageId;

	/** @var string */
	private $content;

	/** @var int */
	private $createdAt;

	public function __construct( IdentifiesMessage $messageId, string $content, ?int $createdAt = null )
	{
		$this->messageId = $messageId;
		$this->content   = $content;
		$this->createdAt = $createdAt ?? (int)(microtime( true ) * 10000);
	}

	public function getMessageId() : IdentifiesMessage
	{
		return $this->messageId;
	}

	public function getContent() : string
	{
		return $this->content;
	}

	public function createdAt() : int
	{
		return $this->createdAt;
	}
}
