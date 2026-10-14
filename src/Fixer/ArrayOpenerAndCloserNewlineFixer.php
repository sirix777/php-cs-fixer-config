<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use function krsort;
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
        $borders = [];

        for ($i = $tokens->count() - 1; $i >= 0; --$i) {
            if (! $this->isArray($tokens, $i)) {
                continue;
            }

            $array                    = $this->getArrayInfo($tokens, $i);
            $borders[$array['start']] = [$array['start'], $array['end']];
            $borders[$array['end']]   = [$array['start'], $array['end']];
        }

        // Keep the original bounds and border order used by the 1.2.0 formatter.
        krsort($borders);

        foreach ($borders as $position => [$start, $end]) {
            $next = $tokens->getNextMeaningfulToken($start);

            if ($this->isArray($tokens, $next) || ! $this->hasKeyedItem($tokens, $start, $end)) {
                continue;
            }

            $isOpener        = $position === $start;
            $whitespaceIndex = $isOpener ? $start + 1 : $end - 1;

            if (! str_contains($tokens[$whitespaceIndex]->getContent(), "\n")) {
                $tokens->ensureWhitespaceAtIndex($whitespaceIndex, $isOpener ? 0 : 1, $this->whitespacesConfig->getLineEnding());
            }
        }
    }

    private function hasKeyedItem(Tokens $tokens, int $start, int $end): bool
    {
        for ($i = $end - 1; $i > $start; --$i) {
            $block = Tokens::detectBlockType($tokens[$i]);

            if (null !== $block && ! $block['isStart']) {
                $i = $tokens->findBlockStart($block['type'], $i);

                continue;
            }

            if ($tokens[$i]->isGivenKind(T_DOUBLE_ARROW)) {
                return true;
            }
        }

        return false;
    }
}
