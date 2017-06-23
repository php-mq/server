<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Constants;

/**
 * Class AnsiColors
 * @package PHPMQ\Server\Constants
 */
abstract class AnsiColors
{
	public const COLORS = [
		'<:fg>'        => "\e[39m",
		'<:bg>'        => "\e[49m",
		'<fg:red>'     => "\e[31m",
		'<fg:green>'   => "\e[32m",
		'<fg:yellow>'  => "\e[33m",
		'<fg:blue>'    => "\e[34m",
		'<fg:magenta>' => "\e[35m",
		'<fg:cyan>'    => "\e[36m",
		'<bg:red>'     => "\e[41m",
		'<bg:green>'   => "\e[42m",
		'<bg:yellow>'  => "\e[43m",
		'<bg:blue>'    => "\e[44m",
		'<bg:magenta>' => "\e[45m",
		'<bg:cyan>'    => "\e[46m",
	];
}
