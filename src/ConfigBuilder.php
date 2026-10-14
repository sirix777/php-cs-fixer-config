<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig;

use BadMethodCallException;
use Gordinskiy\LineLengthChecker\Rules\LineLengthLimit;
use PedroTroller\CS\Fixer\CodingStyle\LineBreakBetweenMethodArgumentsFixer;
use PedroTroller\CS\Fixer\CodingStyle\LineBreakBetweenStatementsFixer;
use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;
use Sirix\CsFixerConfig\Fixer\ArrayListItemNewlineFixer;
use Sirix\CsFixerConfig\Fixer\ArrayOpenerAndCloserNewlineFixer;
use Sirix\CsFixerConfig\Fixer\StandaloneLineInMultilineArrayFixer;

use function array_merge;
use function call_user_func_array;
use function get_class;
use function is_callable;
use function sprintf;

/**
 * @method self setRiskyAllowed(bool $flag)
 * @method self setUsingCache(bool $flag)
 */
final class ConfigBuilder
{
    public const RULE_ARRAY_OPENER_AND_CLOSER_NEWLINE    = 'Symplify/array_opener_and_closer_newline';
    public const RULE_ARRAY_LIST_ITEM_NEWLINE            = 'Symplify/array_list_item_newline';
    public const RULE_STANDALONE_LINE_IN_MULTILINE_ARRAY = 'Symplify/standalone_line_in_multiline_array';
    private const DEFAULT_CONFIG_NAME                    = 'Sirix';

    /** @var array<string, string> */
    private const RULE_ALIASES = [
        'Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer'    => self::RULE_ARRAY_OPENER_AND_CLOSER_NEWLINE,
        'Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer'           => self::RULE_ARRAY_LIST_ITEM_NEWLINE,
        'Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer' => self::RULE_STANDALONE_LINE_IN_MULTILINE_ARRAY,
    ];

    private array $rules = [
        '@PER-CS3x0'                                        => true,
        '@PhpCsFixer'                                       => true,
        'array_syntax'                                      => [
            'syntax' => 'short',
        ],
        'array_indentation'                                 => true,
        'trailing_comma_in_multiline'                       => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
                'array_destructuring',
                'match',
            ],
        ],
        'ordered_imports'                                   => [
            'sort_algorithm' => 'alpha',
            'imports_order'  => [
                'const',
                'class',
                'function',
            ],
        ],
        'declare_strict_types'                              => true,
        'no_unused_imports'                                 => true,
        'function_declaration'                              => [
            'closure_function_spacing' => 'none',
        ],
        'not_operator_with_successor_space'                 => true,
        'concat_space'                                      => [
            'spacing' => 'one',
        ],
        self::RULE_ARRAY_OPENER_AND_CLOSER_NEWLINE          => true,
        self::RULE_ARRAY_LIST_ITEM_NEWLINE                  => true,
        self::RULE_STANDALONE_LINE_IN_MULTILINE_ARRAY       => true,
        'native_function_invocation'                        => [
            'include' => ['@all'],
            'scope'   => 'all',
            'strict'  => true,
        ],
        'binary_operator_spaces'                            => [
            'operators' => [
                '='    => 'align_single_space',
                '=>'   => 'align_by_scope',
            ],
        ],
        'global_namespace_import'                           => [
            'import_classes'   => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'PedroTroller/line_break_between_method_arguments'  => [
            'max-args'                 => 4,
            'max-length'               => 160,
            'automatic-argument-merge' => true,
            'inline-attributes'        => true,
        ],
        'PedroTroller/line_break_between_statements'        => true,
        'phpdoc_to_comment'                                 => false,
    ];

    private Config $config;
    private bool $customFixersRegistered = false;

    private function __construct()
    {
        $this->config = new Config(self::DEFAULT_CONFIG_NAME);
        $this->config
            ->setRiskyAllowed(true)
            ->setUsingCache(true)
        ;
    }

    /**
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): self
    {
        $method = [$this->config, $name];

        if (is_callable($method)) {
            call_user_func_array($method, $arguments);

            return $this;
        }

        throw new BadMethodCallException(sprintf('Method "%s::%s" does not exists.', get_class($this->config), $name));
    }

    public static function create(): self
    {
        return new self();
    }

    public function inDir(string $dir): self
    {
        $this->getFinder()->in([$dir]);

        return $this;
    }

    public function addFiles(array $files): self
    {
        $this->getFinder()->append($files);

        return $this;
    }

    public function setRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $this->resolveRuleNames($rules));

        return $this;
    }

    public function getConfig(): ConfigInterface
    {
        $this->registerCustomFixers();

        return $this->config->setRules($this->rules);
    }

    public function setFinder(Finder $finder): self
    {
        $this->config->setFinder($finder);

        return $this;
    }

    private function getFinder(): Finder
    {
        return $this->config->getFinder();
    }

    private function resolveRuleNames(array $rules): array
    {
        $resolved = [];

        foreach ($rules as $name => $value) {
            $resolved[self::RULE_ALIASES[$name] ?? $name] = $value;
        }

        return $resolved;
    }

    private function registerCustomFixers(): void
    {
        if ($this->customFixersRegistered) {
            return;
        }

        $this->config->registerCustomFixers([
            new LineLengthLimit(),
            new LineBreakBetweenMethodArgumentsFixer(),
            new LineBreakBetweenStatementsFixer(),
            new ArrayOpenerAndCloserNewlineFixer(),
            new ArrayListItemNewlineFixer(),
            new StandaloneLineInMultilineArrayFixer(),
        ]);
        $this->customFixersRegistered = true;
    }
}
