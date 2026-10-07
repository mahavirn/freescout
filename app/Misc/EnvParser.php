<?php
/**
 * Standard phpdotenv parser which also reads .env files created before Laravel 13.
 *
 * Old FreeScout versions (phpdotenv v2) wrote and accepted values which phpdotenv v5
 * rejects or reads differently:
 * - backslashes in double quotes: DB_PASSWORD="pa\ss";
 * - unescaped quotes: APP_X="value with "inner" quotes" (https://github.com/freescout-helpdesk/freescout/issues/2822);
 * - "#" inside unquoted values: DB_PASSWORD=Pa#ss (only " #" starts a comment).
 * Only such lines are converted (using the old rules), all other lines are parsed as is.
 * New values are written in standard syntax by Helper::setEnvFileVar().
 */

namespace App\Misc;

use Dotenv\Parser\Parser;
use Dotenv\Parser\ParserInterface;

class EnvParser implements ParserInterface
{
    /**
     * Read .env file variables without changing the environment.
     * Used by public/install.php and public/tools.php which work without booting the app.
     */
    public static function readFile($dir, $file = '.env')
    {
        $store = \Dotenv\Store\StoreBuilder::createWithNoNames()->addPath($dir)->addName($file)->make();
        $repository = \Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(\Dotenv\Repository\Adapter\ArrayAdapter::class)
            ->make();

        try {
            return (new \Dotenv\Dotenv($store, new self(), new \Dotenv\Loader\Loader(), $repository))->load();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function parse(string $content)
    {
        $lines = preg_split("/(\r\n|\n|\r)/", $content);

        foreach ($lines as $i => $line) {
            $lines[$i] = self::convertLegacyLine($line);
        }

        return (new Parser())->parse(implode("\n", $lines));
    }

    /**
     * Convert a line which phpdotenv v5 rejects or reads differently than phpdotenv v2.
     */
    public static function convertLegacyLine($line)
    {
        if (!preg_match('/^(\s*(?:export\s+)?[a-zA-Z0-9_.]+\s*=\s*)(.*)$/', $line, $m)) {
            return $line;
        }
        list(, $name, $value) = $m;

        // Unquoted value with "#" which is not a comment.
        if ($value !== '' && $value[0] != '"' && $value[0] != "'") {
            $value = trim(explode(' #', $value, 2)[0]);
            if (strpos($value, '#') === false || preg_match('/\s/', $value)) {
                return $line;
            }

            return $name.self::encode($value);
        }

        // Quoted value which phpdotenv v5 can not parse.
        if ($value !== '' && !self::isValidLine($line)) {
            return $name.self::encode(self::decodeQuotedValue(trim($value)));
        }

        return $line;
    }

    protected static function isValidLine($line)
    {
        try {
            $entries = (new Parser())->parse($line);
        } catch (\Dotenv\Exception\InvalidFileException $e) {
            return false;
        }

        return count($entries) == 1 && $entries[0]->getValue()->isDefined();
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
