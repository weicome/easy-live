<?php

namespace EasySwoole\DoctrineAnnotation\Tests;

use EasySwoole\DoctrineAnnotation\DocLexer;
use PHPUnit\Framework\TestCase;

use function str_repeat;

class DocLexerTest extends TestCase
{
    public function testMarkerAnnotation(): void
    {
        $lexer = new DocLexer();

        $lexer->setInput('@Name');
        self::assertNull($lexer->token);
        self::assertNull($lexer->lookahead);

        self::assertTrue($lexer->moveNext());
        self::assertNull($lexer->token);
        self::assertEquals('@', $lexer->lookahead['value']);

        self::assertTrue($lexer->moveNext());
        self::assertEquals('@', $lexer->token['value']);
        self::assertEquals('Name', $lexer->lookahead['value']);

        self::assertFalse($lexer->moveNext());
    }

    public function testScannerTokenizesDocBlockWhitConstants(): void
    {
        $lexer    = new DocLexer();
        $docblock = '@AnnotationWithConstants(
            PHP_EOL,
            ClassWithConstants::SOME_VALUE,
            ClassWithConstants::CONSTANT_,
            ClassWithConstants::CONST_ANT3,
            \EasySwoole\DoctrineAnnotation\Tests\Fixtures\InterfaceWithConstants::SOME_VALUE
        )';

        $tokens = [
            [
                'value'     => '@',
                'position'  => 0,
                'type'      => DocLexer::T_AT,
            ],
            [
                'value'     => 'AnnotationWithConstants',
                'position'  => 1,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => '(',
                'position'  => 24,
                'type'      => DocLexer::T_OPEN_PARENTHESIS,
            ],
            [
                'value'     => 'PHP_EOL',
                'position'  => 38,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 45,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => 'ClassWithConstants::SOME_VALUE',
                'position'  => 59,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 89,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => 'ClassWithConstants::CONSTANT_',
                'position'  => 103,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 132,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => 'ClassWithConstants::CONST_ANT3',
                'position'  => 146,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 176,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => '\EasySwoole\DoctrineAnnotation\Tests\Fixtures\InterfaceWithConstants::SOME_VALUE',
                'position'  => 190,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ')',
                'position'  => 279,
                'type'      => DocLexer::T_CLOSE_PARENTHESIS,
            ],
        ];

        $lexer->setInput($docblock);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $lookahead = $lexer->lookahead;
            self::assertEquals($expected['value'], $lookahead['value']);
            self::assertEquals($expected['type'], $lookahead['type']);
            self::assertEquals($expected['position'], $lookahead['position']);
        }

        self::assertFalse($lexer->moveNext());
    }

    public function testScannerTokenizesDocBlockWhitInvalidIdentifier(): void
    {
        $lexer    = new DocLexer();
        $docblock = '@Foo\3.42';

        $tokens = [
            [
                'value'     => '@',
                'position'  => 0,
                'type'      => DocLexer::T_AT,
            ],
            [
                'value'     => 'Foo',
                'position'  => 1,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => '\\',
                'position'  => 4,
                'type'      => DocLexer::T_NAMESPACE_SEPARATOR,
            ],
            [
                'value'     => 3.42,
                'position'  => 5,
                'type'      => DocLexer::T_FLOAT,
            ],
        ];

        $lexer->setInput($docblock);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $lookahead = $lexer->lookahead;
            self::assertEquals($expected['value'], $lookahead['value']);
            self::assertEquals($expected['type'], $lookahead['type']);
            self::assertEquals($expected['position'], $lookahead['position']);
        }

        self::assertFalse($lexer->moveNext());
    }

    /**
     * @group 44
     */
    public function testWithinDoubleQuotesVeryVeryLongStringWillNotOverflowPregSplitStackLimit(): void
    {
        $lexer = new DocLexer();

        $lexer->setInput('"' . str_repeat('.', 20240) . '"');

        self::assertIsArray($lexer->glimpse());
    }

    /**
     * @group 44
     */
    public function testRecognizesDoubleQuotesEscapeSequence(): void
    {
        $lexer    = new DocLexer();
        $docblock = '@Foo("""' . "\n" . '""")';

        $tokens = [
            [
                'value'     => '@',
                'position'  => 0,
                'type'      => DocLexer::T_AT,
            ],
            [
                'value'     => 'Foo',
                'position'  => 1,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => '(',
                'position'  => 4,
                'type'      => DocLexer::T_OPEN_PARENTHESIS,
            ],
            [
                'value'     => "\"\n\"",
                'position'  => 5,
                'type'      => DocLexer::T_STRING,
            ],
            [
                'value'     => ')',
                'position'  => 12,
                'type'      => DocLexer::T_CLOSE_PARENTHESIS,
            ],
        ];

        $lexer->setInput($docblock);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $lookahead = $lexer->lookahead;
            self::assertEquals($expected['value'], $lookahead['value']);
            self::assertEquals($expected['type'], $lookahead['type']);
            self::assertEquals($expected['position'], $lookahead['position']);
        }

        self::assertFalse($lexer->moveNext());
    }

    public function testDoesNotRecognizeFullAnnotationWithDashInIt(): void
    {
        $this->expectDocblockTokens(
            '@foo-bar--',
            [
                [
                    'value'     => '@',
                    'position'  => 0,
                    'type'      => DocLexer::T_AT,
                ],
                [
                    'value'     => 'foo',
                    'position'  => 1,
                    'type'      => DocLexer::T_IDENTIFIER,
                ],
                [
                    'value'     => '-',
                    'position'  => 4,
                    'type'      => DocLexer::T_MINUS,
                ],
                [
                    'value'     => 'bar',
                    'position'  => 5,
                    'type'      => DocLexer::T_IDENTIFIER,
                ],
                [
                    'value'     => '-',
                    'position'  => 8,
                    'type'      => DocLexer::T_MINUS,
                ],
                [
                    'value'     => '-',
                    'position'  => 9,
                    'type'      => DocLexer::T_MINUS,
                ],
            ]
        );
    }

    public function testRecognizesNegativeNumbers(): void
    {
        $this->expectDocblockTokens(
            '-12.34 -56',
            [
                [
                    'value'     => '-12.34',
                    'position'  => 0,
                    'type'      => DocLexer::T_FLOAT,
                ],
                [
                    'value'     => '-56',
                    'position'  => 7,
                    'type'      => DocLexer::T_INTEGER,
                ],
            ]
        );
    }

    /**
     * @phpstan-param list<array{value: mixed, position: int, type:int}> $expectedTokens
     */
    private function expectDocblockTokens(string $docBlock, array $expectedTokens): void
    {
        $lexer = new DocLexer();
        $lexer->setInput($docBlock);

        $actualTokens = [];

        while ($lexer->moveNext()) {
            $lookahead = $lexer->lookahead;

            $actualTokens[] = [
                'value' => $lookahead['value'],
                'type' => $lookahead['type'],
                'position' => $lookahead['position'],
            ];
        }

        self::assertEquals($expectedTokens, $actualTokens);
    }

    public function testTokenAdjacency(): void
    {
        $lexer = new DocLexer();

        $lexer->setInput('-- -');

        self::assertTrue($lexer->nextTokenIsAdjacent());
        self::assertTrue($lexer->moveNext());
        self::assertTrue($lexer->nextTokenIsAdjacent());
        self::assertTrue($lexer->moveNext());
        self::assertTrue($lexer->nextTokenIsAdjacent());
        self::assertTrue($lexer->moveNext());
        self::assertFalse($lexer->nextTokenIsAdjacent());
        self::assertFalse($lexer->moveNext());
    }
}
