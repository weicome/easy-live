<?php

namespace EasySwoole\Validate\tests;

/**
 * 是否一个能连通的URL
 * Class ActiveUrlTest
 *
 * @internal
 */
class ActiveUrlTest extends BaseTestCase
{
    // 合法断言
    public function testValidCase()
    {
        // 可以连通的网址
        $this->freeValidate();
        $this->validate->addColumn('url')->activeUrl();
        $validateResult = $this->validate->validate(['url' => 'http://baidu.com']);
        $this->assertTrue($validateResult);
    }

    // 默认错误信息断言
    public function testDefaultErrorMsgCase()
    {
        // 有效网址但不能连通
        $this->freeValidate();
        $this->validate->addColumn('url', '网站')->activeUrl();
        $validateResult = $this->validate->validate(['url' => 'http://xxx.cn']);
        $this->assertFalse($validateResult);
        $this->assertEquals('网站必须是可访问的网址', $this->validate->getError()->__toString());

        // 无效的网址
        $this->freeValidate();
        $this->validate->addColumn('url')->activeUrl();
        $validateResult = $this->validate->validate(['url' => 'this is not a url']);
        $this->assertFalse($validateResult);
        $this->assertEquals('url必须是可访问的网址', $this->validate->getError()->__toString());
    }

    // 自定义错误信息断言
    public function testCustomErrorMsgCase()
    {
        $this->freeValidate();
        $this->validate->addColumn('url', '网站')->activeUrl('您输入的网址无效');
        $validateResult = $this->validate->validate(['url' => 'http://xxx.cn']);
        $this->assertFalse($validateResult);
        $this->assertEquals('您输入的网址无效', $this->validate->getError()->__toString());
    }
}
