<?php

/*
 * Monolog Cli formatter
 *
 * (c) Pavel Tetyaev <pahanini@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace pahanini\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;

/**
 * Monolog CLI Formatter.
 *
 * Useful for tests
 */
class CliFormatter extends NormalizerFormatter
{
    public const TAB = '    ';
    public const COLORS = [
        LOGGER::DEBUG     => '0;2',
        LOGGER::INFO      => '0;32',
        LOGGER::NOTICE    => '1;33',
        LOGGER::WARNING   => '0;35',
        LOGGER::ERROR     => '0;31',
        LOGGER::CRITICAL  => ['0;30', '43'],
        LOGGER::ALERT     => ['1;37', '45'],
        LOGGER::EMERGENCY => ['1;37', '41'],
    ];

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $record = parent::format($record);
        $lines = [Logger::getLevelName($record['level']).' : '.$record['message']];
        if (!empty($record['context'])) {
            $context = json_encode($record['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $lines = array_merge($lines, explode(PHP_EOL, trim($context)));
        }
        $max = max(array_map('strlen', $lines));
        for ($i = 1, $iMax = count($lines); $i < $iMax; $i++) {
            $lines[$i] = static::TAB.str_pad($lines[$i], $max + 5);
        }
        $string = implode(PHP_EOL, $lines);
        $colors = static::COLORS[$record['level']];
        if (is_array($colors)) {
            $pad = PHP_EOL.str_repeat(static::TAB.str_repeat(' ', $max + 5).PHP_EOL, 2);

            return "\n\033[{$colors[0]}m\033[{$colors[1]}m".$pad.$string.$pad."\033[0m";
        }

        return "\n\033[{$colors}m".$string."\033[0m";
    }
}
