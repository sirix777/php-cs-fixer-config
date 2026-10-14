<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Tests;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sirix\CsFixerConfig\Fixer\ArrayListItemNewlineFixer;
use Sirix\CsFixerConfig\Fixer\ArrayOpenerAndCloserNewlineFixer;
use Sirix\CsFixerConfig\Fixer\StandaloneLineInMultilineArrayFixer;
use SplFileInfo;

/**
 * @coversNothing
 *
 * @internal
 */
final class ArrayFixerTest extends TestCase
{
    /** @param class-string<FixerInterface> $class */
    #[DataProvider('formattingCases')]
    public function testFormatsArraysWithoutExternalServices(string $class, string $input, string $expected): void
    {
        $fixer  = new $class();
        $tokens = Tokens::fromCode($input);
        $fixer->fix(new SplFileInfo('fixture.php'), $tokens);

        self::assertSame($expected, $tokens->generateCode());

        Tokens::clearCache();
        $tokens = Tokens::fromCode($expected);
        $fixer->fix(new SplFileInfo('fixture.php'), $tokens);

        self::assertSame($expected, $tokens->generateCode(), 'Formatting must be idempotent.');
    }

    /** @return iterable<string, array{class-string<FixerInterface>, string, string}> */
    public static function formattingCases(): iterable
    {
        yield 'brackets around keyed array' => [
            ArrayOpenerAndCloserNewlineFixer::class,
            "<?php \$a = ['one' => 1, 'two' => 2];",
            "<?php \$a = [\n'one' => 1, 'two' => 2\n];",
        ];

        yield 'commas between keyed items' => [
            ArrayListItemNewlineFixer::class,
            "<?php \$a = ['one' => foo(1, 2), 'two' => 2];",
            "<?php \$a = ['one' => foo(1, 2),\n'two' => 2];",
        ];

        yield 'standalone keyed items' => [
            StandaloneLineInMultilineArrayFixer::class,
            "<?php \$a = ['one' => 1, 'two' => 2];",
            "<?php \$a = [\n    'one' => 1,\n    'two' => 2\n];",
        ];

        yield 'nested keyed values' => [
            StandaloneLineInMultilineArrayFixer::class,
            "<?php \$a = ['nested' => ['one' => 1, 'two' => 2], 'list' => [1, 2]];",
            "<?php \$a = [\n    'nested' => [\n        'one' => 1,\n        'two' => 2\n    ],\n    'list' => [1, 2]\n];",
        ];

        yield 'long syntax' => [
            StandaloneLineInMultilineArrayFixer::class,
            "<?php \$a = array('one' => 1, 'two' => 2);",
            "<?php \$a = array(\n    'one' => 1,\n    'two' => 2\n);",
        ];

        yield 'trailing comma' => [
            ArrayListItemNewlineFixer::class,
            "<?php \$a = ['one' => 1, 'two' => 2,];",
            "<?php \$a = ['one' => 1,\n'two' => 2,\n];",
        ];

        yield 'inline comment stays with its item' => [
            StandaloneLineInMultilineArrayFixer::class,
            "<?php \$a = ['one' => 1, /* keep */ 'two' => 2];",
            "<?php \$a = [\n    'one' => 1, /* keep */ 'two' => 2\n];",
        ];

        yield 'closure values' => [
            ArrayListItemNewlineFixer::class,
            "<?php \$a = ['one' => function () { return [1, 2]; }, 'two' => 2];",
            "<?php \$a = ['one' => function () { return [1, 2]; },\n'two' => 2];",
        ];

        yield 'match arms are not array items' => [
            ArrayListItemNewlineFixer::class,
            "<?php \$a = ['one' => match (\$v) { 1, 2 => 'x', default => 'y' }, 'two' => 2];",
            "<?php \$a = ['one' => match (\$v) { 1, 2 => 'x', default => 'y' },\n'two' => 2];",
        ];

        yield 'nested brackets remain paired' => [
            ArrayOpenerAndCloserNewlineFixer::class,
            "<?php \$a = ['nested' => ['one' => 1, 'two' => 2], 'list' => [1, 2]];",
            "<?php \$a = [\n'nested' => [\n'one' => 1, 'two' => 2\n], 'list' => [1, 2]\n];",
        ];

        yield 'existing multiline layout' => [
            StandaloneLineInMultilineArrayFixer::class,
            "<?php\n\$a = [\n    'one' => 1, 'two' => 2,\n];",
            "<?php\n\$a = [\n    'one' => 1,\n    'two' => 2,\n];",
        ];

        yield 'line comment stays with its item' => [
            ArrayListItemNewlineFixer::class,
            "<?php \$a = ['one' => 1, // keep\n 'two' => 2];",
            "<?php \$a = ['one' => 1, // keep\n 'two' => 2];",
        ];

        yield 'standalone single item remains inline' => [
            StandaloneLineInMultilineArrayFixer::class,
            "<?php \$a = ['one' => 1];",
            "<?php \$a = ['one' => 1];",
        ];

        foreach ([ArrayOpenerAndCloserNewlineFixer::class, ArrayListItemNewlineFixer::class, StandaloneLineInMultilineArrayFixer::class] as $class) {
            foreach ([
                'empty'         => '<?php $a = [];',
                'list'          => '<?php $a = [1, 2, 3];',
                'destructuring' => "<?php ['one' => \$one, 'two' => \$two] = \$a;",
                'offset'        => "<?php \$a['one'];",
            ] as $name => $input) {
                yield $class . ' ' . $name => [$class, $input, $input];
            }
        }
    }

    public function testRespectsWhitespaceConfiguration(): void
    {
        $fixer = new StandaloneLineInMultilineArrayFixer();
        $fixer->setWhitespacesConfig(new WhitespacesFixerConfig("\t", "\r\n"));
        $tokens = Tokens::fromCode("<?php\r\n\t\$a = ['one' => 1, 'two' => 2];");
        $fixer->fix(new SplFileInfo('fixture.php'), $tokens);

        self::assertSame("<?php\r\n\t\$a = [\r\n\t\t'one' => 1,\r\n\t\t'two' => 2\r\n\t];", $tokens->generateCode());
    }
}
