<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Types;

use hollodotme\PHPMQ\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;

/**
 * Class Message
 * @package hollodotme\PHPMQ\Types
 */
final class Message implements CarriesInformation
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
