<?php

namespace EasySwoole\Validate\Functions;

use EasySwoole\Validate\Validate;

class NotInArray extends AbstractValidateFunction
{
    public function name(): string
    {
        return 'NotInArray';
    }

    public function validate($itemData, $arg, $column, Validate $validate): bool
    {
        $array = array_shift($arg);
        $isStrict = array_shift($arg);

        return !in_array($itemData, $array, $isStrict);
    }
}
