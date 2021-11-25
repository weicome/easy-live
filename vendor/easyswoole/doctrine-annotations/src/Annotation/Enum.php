<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace EasySwoole\DoctrineAnnotation\Annotation;

use InvalidArgumentException;

use function get_class;
use function gettype;
use function in_array;
use function is_object;
use function is_scalar;
use function sprintf;

/**
 * Annotation that can be used to signal to the parser
 * to check the available values during the parsing process.
 *
 * @Annotation
 * @Attributes({
 *    @Attribute("value",   required = true,  type = "array"),
 *    @Attribute("literal", required = false, type = "array")
 * })
 */
final class Enum
{
    /** @phpstan-var list<scalar> */
    public $value;

    /**
     * Literal target declaration.
     *
     * @var mixed[]
     */
    public $literal;

    /**
     * @throws InvalidArgumentException
     *
     * @phpstan-param array{literal?: mixed[], value: list<scalar>} $values
     */
    public function __construct(array $values)
    {
        if (! isset($values['literal'])) {
            $values['literal'] = [];
        }

        foreach ($values['value'] as $var) {
            if (! is_scalar($var)) {
                throw new InvalidArgumentException(sprintf(
                    '@Enum supports only scalar values "%s" given.',
                    is_object($var) ? get_class($var) : gettype($var)
                ));
            }
        }

        foreach ($values['literal'] as $key => $var) {
            if (! in_array($key, $values['value'])) {
                throw new InvalidArgumentException(sprintf(
                    'Undefined enumerator value "%s" for literal "%s".',
                    $key,
                    $var
                ));
            }
        }

        $this->value   = $values['value'];
        $this->literal = $values['literal'];
    }
}
