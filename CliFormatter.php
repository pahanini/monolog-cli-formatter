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
use Monolog\LogRecord;
use Monolog\Level;

/**
 * Monolog CLI Formatter.
 *
 * Useful for tests
 */
class CliFormatter extends NormalizerFormatter
{
    public const TAB = '    ';
    public static $COLORS = [];

    public function __construct()
    {
        parent::__construct();

        static::$COLORS = [
            Level::Debug->value     => '0;2',
            Level::Info->value      => '0;32',
            Level::Notice->value    => '1;33',
            Level::Warning->value   => '0;35',
            Level::Error->value     => '0;31',
            Level::Critical->value  => ['0;30', '43'],
            Level::Alert->value     => ['1;37', '45'],
            Level::Emergency->value => ['1;37', '41'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function format(LogRecord $record)
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $record = parent::format($record);
        $lines = [Level::fromValue($record['level'])->getName() .' : '.$record['message']];
        if (!empty($record['context'])) {
            $context = json_encode($record['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $lines = array_merge($lines, explode(PHP_EOL, trim($context)));
        }
        $max = max(array_map('strlen', $lines));
        for ($i = 1, $iMax = count($lines); $i < $iMax; $i++) {
            $lines[$i] = static::TAB.str_pad($lines[$i], $max + 5);
        }
        $string = implode(PHP_EOL, $lines);
        $colors = static::$COLORS[$record['level']];
        if (is_array($colors)) {
            $pad = PHP_EOL.str_repeat(static::TAB.str_repeat(' ', $max + 5).PHP_EOL, 2);

            return "\n\033[{$colors[0]}m\033[{$colors[1]}m".$pad.$string.$pad."\033[0m";
        }

        return "\n\033[{$colors}m".$string."\033[0m";
    }
}
