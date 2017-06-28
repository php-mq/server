<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers\Constants;

use Psr\Log\LogLevel as PsrLogLevel;

/**
 * Class LogLevel
 * @package PHPMQ\Server\Loggers\Constants
 */
abstract class LogLevel
{
	public const LOG_LEVEL_ERROR = 'error';

	public const LOG_LEVEL_INFO  = 'info';

	public const LOG_LEVEL_DEBUG = 'debug';

	public const LOG_LEVEL_ASSOC = [
		self::LOG_LEVEL_ERROR => [
			PsrLogLevel::CRITICAL,
			PsrLogLevel::EMERGENCY,
			PsrLogLevel::ERROR,
			PsrLogLevel::ALERT,
		],
		self::LOG_LEVEL_INFO  => [
			PsrLogLevel::CRITICAL,
			PsrLogLevel::EMERGENCY,
			PsrLogLevel::ERROR,
			PsrLogLevel::ALERT,
			PsrLogLevel::WARNING,
			PsrLogLevel::NOTICE,
			PsrLogLevel::INFO,
		],
		self::LOG_LEVEL_DEBUG => [
			PsrLogLevel::CRITICAL,
			PsrLogLevel::EMERGENCY,
			PsrLogLevel::ERROR,
			PsrLogLevel::ALERT,
			PsrLogLevel::WARNING,
			PsrLogLevel::NOTICE,
			PsrLogLevel::INFO,
			PsrLogLevel::DEBUG,
		],
	];
}
