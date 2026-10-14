<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use function array_reverse;
use function str_contains;

/** Array formatting adapted from symplify/coding-standard (MIT); see LICENSE. */
final class ArrayListItemNewlineFixer extends AbstractArrayNewlineFixer
{
    public const RULE_NAME = 'Symplify/array_list_item_newline';

    public static function getRuleName(): string
    {
        return self::RULE_NAME;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Keyed array items must be on separate lines.', []);
    }

    public function getPriority(): int
    {
        return 40;
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($i = $tokens->count() - 1; $i >= 0; --$i) {
            if (! $this->isArray($tokens, $i)) {
                continue;
            }

            $array = $this->getArrayInfo($tokens, $i);

            if ([] === $array['arrows']) {
                continue;
            }

            foreach (array_reverse($array['commas']) as $comma) {
                if (str_contains($tokens[$comma + 1]->getContent(), "\n")) {
                    continue;
                }

                $next = $tokens->getNonWhitespaceSibling($comma, 1, " \t\r\0\x0B");

                if (null !== $next && $tokens[$next]->isGivenKind(T_COMMENT)) {
                    continue;
                }

                $tokens->ensureWhitespaceAtIndex($comma + 1, 0, $this->whitespacesConfig->getLineEnding());
            }
        }
    }
}
