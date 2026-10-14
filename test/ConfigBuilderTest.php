<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Tests;

use LogicException;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sirix\CsFixerConfig\ConfigBuilder;
use Sirix\CsFixerConfig\Fixer\ArrayListItemNewlineFixer;
use Sirix\CsFixerConfig\Fixer\ArrayOpenerAndCloserNewlineFixer;
use Sirix\CsFixerConfig\Fixer\StandaloneLineInMultilineArrayFixer;
use SplFileInfo;
use Traversable;

use function array_map;
use function iterator_to_array;
use function realpath;

/**
 * @internal
 *
 * @coversNothing
 */
class ConfigBuilderTest extends TestCase
{
    private ConfigBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = ConfigBuilder::create();
    }

    #[Test]
    public function itRetrievesDefaultOptions(): void
    {
        $config = $this->builder->getConfig();

        self::assertEquals('Sirix', $config->getName());
        self::assertTrue($config->getRiskyAllowed());
        self::assertTrue($config->getUsingCache());
    }

    #[Test]
    public function itOverridesDefaultOptions(): void
    {
        /** @var Config $config */
        $config = $this->builder
            ->setRiskyAllowed(false)
            ->setUsingCache(false)
            ->getConfig()
        ;

        self::assertFalse($config->getRiskyAllowed());
        self::assertFalse($config->getUsingCache());
    }

    #[Test]
    public function itHasNoDirectoriesByDefault(): void
    {
        $this->expectException(LogicException::class);

        iterator_to_array($this->builder->getConfig()->getFinder());
    }

    #[Test]
    public function itAddsDirectories(): void
    {
        $finder = $this->builder
            ->inDir(__DIR__ . '/../src')
            ->inDir(__DIR__ . '/../test')
            ->getConfig()
            ->getFinder()
        ;

        $items = $this->finderToArray($finder);

        self::assertContains(__FILE__, $items);
        self::assertContains(realpath(__DIR__ . '/../src/ConfigBuilder.php'), $items);
    }

    #[Test]
    public function itAddsFiles(): void
    {
        $finder = $this->builder
            ->addFiles([__FILE__])
            ->getConfig()
            ->getFinder()
        ;

        $items = $this->finderToArray($finder);

        self::assertCount(1, $items);
        self::assertContains(__FILE__, $items);
    }

    #[Test]
    public function itAddsDefaultRules(): void
    {
        $rules = $this->builder
            ->getConfig()
            ->getRules()
        ;

        self::assertTrue(isset($rules['@PER-CS3x0']));
        self::assertTrue($rules['@PER-CS3x0']);
        self::assertTrue(isset($rules[ArrayOpenerAndCloserNewlineFixer::getRuleName()]));
        self::assertTrue($rules[ArrayOpenerAndCloserNewlineFixer::getRuleName()]);
        self::assertTrue(isset($rules[ArrayListItemNewlineFixer::getRuleName()]));
        self::assertTrue($rules[ArrayListItemNewlineFixer::getRuleName()]);
        self::assertTrue(isset($rules[StandaloneLineInMultilineArrayFixer::getRuleName()]));
        self::assertTrue($rules[StandaloneLineInMultilineArrayFixer::getRuleName()]);
    }

    #[Test]
    public function itMapsSymplifyRuleNames(): void
    {
        $rules = $this->builder
            ->setRules([
                'Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer'    => true,
                'Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer'           => true,
                'Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer' => true,
            ])
            ->getConfig()
            ->getRules()
        ;

        self::assertTrue($rules[ArrayOpenerAndCloserNewlineFixer::getRuleName()]);
        self::assertTrue($rules[ArrayListItemNewlineFixer::getRuleName()]);
        self::assertTrue($rules[StandaloneLineInMultilineArrayFixer::getRuleName()]);
    }

    #[Test]
    public function itFormatsArraysWithTheRegisteredFixers(): void
    {
        $config  = $this->builder->getConfig();
        $factory = (new FixerFactory())
            ->registerBuiltInFixers()
            ->registerCustomFixers($config->getCustomFixers())
            ->useRuleSet(new RuleSet($config->getRules()))
            ->setWhitespacesConfig(new WhitespacesFixerConfig("\t", "\r\n"))
        ;
        $tokens = Tokens::fromCode("<?php\r\ndeclare(strict_types=1);\r\n\r\n\$a = ['one' => 1, 'two' => 2];\r\n");

        foreach ($factory->getFixers() as $fixer) {
            $fixer->fix(new SplFileInfo('fixture.php'), $tokens);
            $tokens->clearEmptyTokens();
        }

        self::assertSame(
            "<?php\r\n\r\ndeclare(strict_types=1);\r\n\r\n\$a = [\r\n\t'one' => 1,\r\n\t'two' => 2,\r\n];\r\n",
            $tokens->generateCode(),
        );
    }

    #[Test]
    public function itDisablesArrayFormattingThroughLegacyAliases(): void
    {
        $config = $this->builder->setRules([
            'Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer'    => false,
            'Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer'           => false,
            'Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer' => false,
        ])->getConfig();
        $factory = (new FixerFactory())
            ->registerBuiltInFixers()
            ->registerCustomFixers($config->getCustomFixers())
            ->useRuleSet(new RuleSet($config->getRules()))
        ;
        $input  = "<?php\n\ndeclare(strict_types=1);\n\n\$a = ['one' => 1, 'two' => 2];\n";
        $tokens = Tokens::fromCode($input);

        foreach ($factory->getFixers() as $fixer) {
            $fixer->fix(new SplFileInfo('fixture.php'), $tokens);
            $tokens->clearEmptyTokens();
        }

        self::assertSame($input, $tokens->generateCode());
    }

    #[Test]
    public function itOverridesDefaultRules(): void
    {
        $rules = $this->builder
            ->setRules([
                '@PER-CS2.0' => false,
            ])
            ->getConfig()
            ->getRules()
        ;

        self::assertTrue(isset($rules['@PER-CS2.0']));
        self::assertFalse($rules['@PER-CS2.0']);
    }

    #[Test]
    public function itSetsFinderAndAccessesDirectly(): void
    {
        $finder = new Finder();
        $finder->in([__DIR__ . '/../test']);

        $configFinder = $this->builder->setFinder($finder)->getConfig()->getFinder();
        $items        = iterator_to_array($configFinder);

        self::assertNotEmpty($items);
        foreach ($items as $file) {
            self::assertStringContainsString(__DIR__, $file->getRealPath());
        }
    }

    #[Test]
    public function itSetsCustomFinderInstance(): void
    {
        $finder = new Finder();
        $finder->in([__DIR__ . '/../src']);

        $config = $this->builder->setFinder($finder)->getConfig();

        self::assertSame($finder, $config->getFinder());
    }

    /**
     * @return array<string>
     */
    private function finderToArray(Traversable $finder): array
    {
        $map = static function(SplFileInfo $info): string {
            return $info->getRealPath();
        };

        return array_map($map, iterator_to_array($finder, false));
    }
}
