<?php

namespace Tests\Unit;

use App\Misc\EnvParser;
use Dotenv\Parser\Parser;
use Tests\TestCase;

class EnvParserTest extends TestCase
{
    private function parse($line, $parser = null)
    {
        $entries = ($parser ?: new EnvParser())->parse($line."\n");

        return $entries[0]->getValue()->get()->getChars();
    }

    /**
     * .env files written before Laravel 13 (phpdotenv v2 rules).
     */
    public function testLegacyValues()
    {
        // Unquoted: only " #" starts a comment.
        $this->assertSame('Pa#ss123', $this->parse('DB_PASSWORD=Pa#ss123'));
        $this->assertSame('value', $this->parse('APP_X=value # comment'));
        // Backslash in double quotes (written by Helper::setEnvFileVar()).
        $this->assertSame('pa\\ss', $this->parse('DB_PASSWORD="pa\\ss"'));
        $this->assertSame('end\\', $this->parse('DB_PASSWORD="end\\"'));
        // Unescaped quotes: https://github.com/freescout-helpdesk/freescout/issues/2822
        $this->assertSame('value with "inner" quotes', $this->parse('APP_X="value with "inner" quotes"'));
        $this->assertSame('pa"ss', $this->parse('DB_PASSWORD="pa\\"ss"'));
        $this->assertSame('single quoted', $this->parse("APP_X='single quoted'"));
        $this->assertSame('', $this->parse('APP_X='));
    }

    /**
     * Values written by FreeScout are valid for the standard phpdotenv parser.
     */
    public function testWrittenValuesAreStandard()
    {
        foreach (['simple', 'pa"ss', 'pa\\ss', 'end\\', 'Pa#ss', 'a b c', 'p@ss!w0rd%^&*()', 'ünïcödé', ''] as $value) {
            $line = 'APP_X='.\Helper::formatEnvValue($value);

            $this->assertSame($value, $this->parse($line, new Parser()), $line);
            $this->assertSame($value, $this->parse($line), $line);
        }
    }
}
