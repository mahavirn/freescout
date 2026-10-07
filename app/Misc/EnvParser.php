<?php
/**
 * .env parser compatible with FreeScout (ported from overrides/vlucas/phpdotenv/src/Loader.php).
 *
 * phpdotenv v5 fails to parse values which FreeScout and its users have in .env files:
 * - backslashes in double quotes: DB_PASSWORD="pa\ss" (written by Helper::setEnvFileVar());
 * - unescaped quotes: APP_X="value with "inner" quotes"
 *   (https://github.com/freescout-helpdesk/freescout/issues/2822).
 * Quoted values are converted using old rules and then parsed by phpdotenv v5.
 */

namespace App\Misc;

use Dotenv\Parser\Parser;
use Dotenv\Parser\ParserInterface;

class EnvParser implements ParserInterface
{
    public function parse(string $content)
    {
        $lines = preg_split("/(\r\n|\n|\r)/", $content);

        foreach ($lines as $i => $line) {
            if (preg_match('/^(\s*(?:export\s+)?[a-zA-Z0-9_.]+\s*=\s*)(["\'].*)$/', $line, $m)) {
                $lines[$i] = $m[1].self::encode(self::decodeQuotedValue(trim($m[2])));
            }
        }

        return (new Parser())->parse(implode("\n", $lines));
    }

    /**
     * Value of a quoted string: everything before the last quote.
     */
    public static function decodeQuotedValue($value)
    {
        $quote = $value[0];

        if (preg_match(sprintf('#\\\\%1$s$#mx', $quote), $value)) {
            $value = rtrim($value, $quote);
        }
        $value = preg_replace(sprintf('/(.*[^\\\\])%1$s[^%1$s]*/mx', $quote), '$1', $value);
        $value = substr($value, 1);

        $value = str_replace("\\$quote", $quote, $value);
        $value = str_replace('\\\\', '\\', $value);

        return $value;
    }

    /**
     * Encode value as double quoted phpdotenv v5 string.
     * $ is not escaped, so ${VAR} is still replaced as before.
     */
    public static function encode($value)
    {
        return '"'.strtr($value, ['\\' => '\\\\', '"' => '\\"']).'"';
    }
}
