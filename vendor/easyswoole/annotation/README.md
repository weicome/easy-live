# annotation

## 实现原理
- 约定
    - 每个注解行为@字符开头，格式为
    ```@METHOD(ARGS)```
- 执行流程如下：
    - 利用php反射得到注释信息
    - 用explode用PHP_EOL分割每行
    - 解析每行的数据，若存在对应的METHOD AnnotationTagInterface ，则把解析得到的ARGS传递给 AnnotationTagInterface 中的 assetValue 方法，
      用户可以在该方法中对解析得到的值进行自定义解析。
    - 若为严格模式，如果解析不到正确的数值，则报错。  

## 例子
```
use EasySwoole\Annotation\Annotation;
use EasySwoole\Annotation\AbstractAnnotationTag;

/*
 * 定义param渲染方法
 */

class param extends AbstractAnnotationTag
{
    public $raw;
    public function tagName(): string
    {
        return 'param';
    }

    public function assetValue(?string $raw)
    {
        $this->raw = $raw;
    }
}

/*
 * 定义timeout渲染方法
 */

class timeout extends AbstractAnnotationTag
{
    public $timeout;

    public function tagName(): string
    {
        return 'timeout';
    }

    public function assetValue(?string $raw)
    {
        $this->timeout = floatval($raw);
    }

    public function aliasMap(): array
    {
        return [
            static::class,'timeout_alias'
        ];
    }
}


class A
{
    /** @timeout()  */
    protected $a;

    /**
     * asdasdasd
     * @param(
        {
            "machineId": "X0592-0010",
           "time": 1582795808,
            "data": {
            "action": "newPhone()",
            "phone": "15505923573",
            "ismi": "ismi12456"
            },
            "signature": "023e301373b4226e6a0ea1bbdadeaa06"
        })
     */
    function test()
    {

    }
}



/*
 * 实例化渲染器,并注册要解析的渲染方法
 */
$annotation = new Annotation();
$ref = new \ReflectionClass(A::class);
//不注册fuck 解析
$annotation->addParserTag(new param());
$annotation->addParserTag(new timeout());
$annotation->addAlias('timeout_alias','timeout');

$list = $annotation->getAnnotation($ref->getMethod('test'));
var_dump($list);

$list = $annotation->getAnnotation($ref->getProperty('a'));

var_dump($list);
```

> 注释每行前3个字符若存在@,说明该行为需要解析注释行，默认为非严格模式，未注册的tag信息不会解析，严格模式下，若无法解析则会抛出异常。

## IDE支持

需要为PHPStorm安装"PHP Annotation"插件以提供注解自动提示能力，插件可以在PHPStorm中直接搜索安装，也可以前往Github下载安装

> https://github.com/Haehnchen/idea-php-annotation-plugin

然后自己编写一个下面这样的注解提示类，重点在于使用@Annotation类注释，标记这是一个注解提示类，PHPStorm索引到该文件，就可以对类名和类的成员进行注解提示

```php

<?php


namespace EasySwoole\Validate;

/**
 * 注解标注文件
 * @Annotation
 * 需要向上方这样使用Annotation标记这是一个注解提示类
 */
final class ValidateRule
{
    /**
     * 括号内会提示这些字段
     * @var string
     */
    protected $name;

    /**
     * 括号内会提示这些字段
     * @var string
     */
    protected $column;

    /**
     * 括号内会提示这些字段
     * @var string
     */
    protected $alias;
}

```

即可实现下面aaa方法的自动注解提示

```php
<?php

use EasySwoole\Validate as Validate;

class a
{
    /**
     * @Validate\ValidateRule(column="name",alias="账号名称")
     */
    function aaa(){

    }
}
```
