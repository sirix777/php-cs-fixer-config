<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer as SymplifyStandaloneLineInMultilineArrayFixer;

/**
 * Adapter for PhpCsFixer-compatible custom fixer naming.
 */
final class StandaloneLineInMultilineArrayFixer implements FixerInterface
{
    public const RULE_NAME = 'Symplify/standalone_line_in_multiline_array';

    public function __construct(private readonly SymplifyStandaloneLineInMultilineArrayFixer $fixer) {}

    public static function getRuleName(): string
    {
        return self::RULE_NAME;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->fixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return $this->fixer->isRisky();
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $this->fixer->fix($file, $tokens);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return $this->fixer->getDefinition();
    }

    public function getName(): string
    {
        return self::RULE_NAME;
    }

    public function getPriority(): int
    {
        return $this->fixer->getPriority();
    }

    public function supports(SplFileInfo $file): bool
    {
        return $this->fixer->supports($file);
    }
}
