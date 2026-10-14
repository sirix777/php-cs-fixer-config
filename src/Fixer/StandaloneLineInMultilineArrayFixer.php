<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use function array_reverse;
use function count;
use function str_contains;

/** Array formatting adapted from symplify/coding-standard (MIT); see LICENSE. */
final class StandaloneLineInMultilineArrayFixer extends AbstractArrayNewlineFixer
{
    public const RULE_NAME = 'Symplify/standalone_line_in_multiline_array';

    public static function getRuleName(): string
    {
        return self::RULE_NAME;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Keyed arrays must have one item per line.', []);
    }

    public function getPriority(): int
    {
        return 5;
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($i = 0; $i < $tokens->count(); ++$i) {
            if (! $this->isArray($tokens, $i)) {
                continue;
            }

            $array = $this->getArrayInfo($tokens, $i);

            // Preserve the legacy traversal: stop at lists and standalone single-item arrays.
            if ([] === $array['arrows']) {
                return;
            }

            if (1 === count($array['arrows'])) {
                $value    = $tokens->getNextNonWhitespace($array['arrows'][0]);
                $previous = $tokens->getPrevMeaningfulToken($array['start']);

                if (! $this->isArray($tokens, $value) && (null === $previous || ! $tokens[$previous]->isGivenKind(T_DOUBLE_ARROW))) {
                    return;
                }
            }

            $lineEnding     = $this->whitespacesConfig->getLineEnding();
            $indent         = WhitespacesAnalyzer::detectIndent($tokens, $array['start']);
            $itemWhitespace = $lineEnding . $indent . $this->whitespacesConfig->getIndent();
            $tokens->ensureWhitespaceAtIndex($array['end'] - 1, 1, $lineEnding . $indent);

            foreach (array_reverse($array['commas']) as $comma) {
                if (str_contains($tokens[$comma + 1]->getContent(), $lineEnding)) {
                    continue;
                }

                if (isset($tokens[$comma + 2]) && $tokens[$comma + 2]->isComment()) {
                    continue;
                }

                $tokens->ensureWhitespaceAtIndex($comma + 1, 0, $itemWhitespace);
            }

            $tokens->ensureWhitespaceAtIndex($array['start'] + 1, 0, $itemWhitespace);
        }
    }
}
