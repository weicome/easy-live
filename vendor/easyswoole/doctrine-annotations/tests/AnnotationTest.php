<?php

namespace EasySwoole\DoctrineAnnotation\Tests;

use BadMethodCallException;
use EasySwoole\DoctrineAnnotation\Annotation;
use PHPUnit\Framework\TestCase;

use function sprintf;

final class AnnotationTest extends TestCase
{
    public function testMagicGetThrowsBadMethodCallException(): void
    {
        $name = 'foo';

        $annotation = new Annotation([]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            "Unknown property '%s' on annotation '%s'.",
            $name,
            Annotation::class
        ));

        $annotation->{$name};
    }

    public function testMagicSetThrowsBadMethodCallException(): void
    {
        $name = 'foo';

        $annotation = new Annotation([]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            "Unknown property '%s' on annotation '%s'.",
            $name,
            Annotation::class
        ));

        $annotation->{$name} = 9001;
    }
}
