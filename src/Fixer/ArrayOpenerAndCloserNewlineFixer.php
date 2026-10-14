<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use function str_contains;

/** Array formatting adapted from symplify/coding-standard (MIT); see LICENSE. */
final class ArrayOpenerAndCloserNewlineFixer extends AbstractArrayNewlineFixer
{
    public const RULE_NAME = 'Symplify/array_opener_and_closer_newline';

    public static function getRuleName(): string
    {
        return self::RULE_NAME;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Keyed array opener and closer must be on their own lines.', []);
    }

    public function getPriority(): int
    {
        return 34;
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($i = $tokens->count() - 1; $i >= 0; --$i) {
            if (! $this->isArray($tokens, $i)) {
                continue;
            }

            $array = $this->getArrayInfo($tokens, $i);
            $next  = $tokens->getNextMeaningfulToken($array['start']);

            if ([] === $array['arrows'] || $this->isArray($tokens, $next)) {
                continue;
            }

            if (! str_contains($tokens[$array['end'] - 1]->getContent(), "\n")) {
                $tokens->ensureWhitespaceAtIndex($array['end'] - 1, 1, $this->whitespacesConfig->getLineEnding());
            }

            if (! str_contains($tokens[$array['start'] + 1]->getContent(), "\n")) {
                $tokens->ensureWhitespaceAtIndex($array['start'] + 1, 0, $this->whitespacesConfig->getLineEnding());
            }
        }
    }
}
