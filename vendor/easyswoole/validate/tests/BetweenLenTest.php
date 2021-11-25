<?php

namespace EasySwoole\Validate\tests;

/**
 * @internal
 */
class BetweenLenTest extends BaseTestCase
{
    /*
     * 合法
     */
    public function testValidCase()
    {
        $this->freeValidate();
        $this->validate->addColumn('name')->betweenLen(2, 6);
        $bool = $this->validate->validate(['name' => 'blank']);
        $this->assertTrue($bool);

        /*
         * file
         */
        $this->freeValidate();
        $this->validate->addColumn('file')->betweenLen(1, 2);
        $bool = $this->validate->validate(['file' => (new UploadFile(__DIR__ . '/../res/easyswoole.png', 1, 200))]);
        $this->assertTrue($bool);
    }

    /*
     * 默认错误信息
     */
    public function testDefaultErrorMsgCase()
    {
        $this->freeValidate();
        $this->freeValidate();
        $this->validate->addColumn('name')->betweenLen(2, 4);
        $bool = $this->validate->validate(['name' => 'blank']);
        $this->assertFalse($bool);
        $this->assertEquals('name的长度只能在 2 - 4 之间', $this->validate->getError()->__toString());
    }

    /*
     * 自定义错误信息
     */
    public function testCustomErrorMsgCase()
    {
        $this->freeValidate();
        $this->freeValidate();
        $this->validate->addColumn('name')->betweenLen(2, 4, '姓名的长度只能2-4位');
        $bool = $this->validate->validate(['name' => 'blank']);
        $this->assertFalse($bool);
        $this->assertEquals('姓名的长度只能2-4位', $this->validate->getError()->__toString());
    }
}
