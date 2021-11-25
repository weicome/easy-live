<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/30
 * Time: 5:41 PM
 */

namespace EasySwoole\Utility;

class ArrayToTextTable
{
    const AlignLeft   = STR_PAD_RIGHT;
    const AlignCenter = STR_PAD_BOTH;
    const AlignRight  = STR_PAD_LEFT;

    protected $data;
    protected $keys;
    protected $widths;
    protected $indentation;
    protected $displayHeader = true;
    protected $keysAlignment;
    protected $valuesAlignment;
    protected $formatter;

    public function __construct($data = [])
    {
        $this->setData($data)
            ->setIndentation('')
            ->setKeysAlignment(self::AlignCenter)
            ->setValuesAlignment(self::AlignLeft)
            ->setFormatter(null);
    }

    public function __toString()
    {
        return $this->getTable();
    }

    public function getTable($data = null)
    {
        if (!is_null($data))
            $this->setData($data);

        $data = $this->prepare();

        $i = $this->indentation;

        $table = $i . $this->line('┌', '─', '┬','┐') . PHP_EOL;

        if($this->displayHeader){
            //绘制table header
            $headerRows = array_combine($this->keys, $this->keys);
            $table .= $i . $this->row($headerRows, $this->keysAlignment) . PHP_EOL;
            $table .= $i . $this->line('├', '─', '┼', '┤') . PHP_EOL;
        }
        $len = count($data);
        for($index = 0;$index < $len;$index ++ ){
            $table .= $i . $this->row($data[$index], $this->valuesAlignment) . PHP_EOL;
            if($index +1 < $len){
                $table .= $i . $this->line('├', '─', '┼', '┤') . PHP_EOL;
            }
        }
        $table .= $i . $this->line('└', '─', '┴', '┘') . PHP_EOL;

        return $table;
    }

    public function setIndentation($indentation)
    {
        $this->indentation = $indentation;
        return $this;
    }

    public function isDisplayHeader(bool $displayHeader)
    {
        $this->displayHeader = $displayHeader;
        return $this;
    }

    public function setKeysAlignment($keysAlignment)
    {
        $this->keysAlignment = $keysAlignment;
        return $this;
    }

    public function setValuesAlignment($valuesAlignment)
    {
        $this->valuesAlignment = $valuesAlignment;
        return $this;
    }

    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    private function line($left, $horizontal, $link, $right) {
        $line = $left;
        foreach ($this->keys as $key){
            $line .= str_repeat($horizontal, $this->widths[$key]+2) . $link;
        }

        if (mb_strlen($line) > mb_strlen($left)){
            $line = mb_substr($line, 0, -mb_strlen($horizontal));
        }
        return $line . $right;
    }

    private function row($row, $alignment) {
        $line = '│';
        foreach ($this->keys as $key) {
            $value = isset($row[$key]) ? $row[$key] : '';
            $line .= ' ' . static::mb_str_pad($value, $this->widths[$key], ' ', $alignment) . ' ' . '│';
        }
        if (empty($row)){
            $line .= '│';
        }
        return $line;
    }

    private function prepare() {
        $this->keys = [];
        $this->widths = [];
        $data = $this->data;

        //合并全部数组的key
        foreach ($data as $row){
            $this->keys = array_merge($this->keys, array_keys($row));
        }
        $this->keys = array_unique($this->keys);

        //补充缺陷数组
        foreach ($data as $index => $row){
            foreach ($this->keys as $key){
                if(!array_key_exists($key,$row)){
                    $data[$index][$key] = null;
                }
            }
        }

        //执行formatter
        if ($this->formatter instanceof \Closure) {
            foreach ($data as &$row){
                array_walk($row, $this->formatter);
            }
            unset($row);
        }

        foreach ($this->keys as $key){
            $this->setWidth($key, $key);
        }
        foreach ($data as $row){
            foreach ($row as $columnKey => $columnValue){
                $this->setWidth($columnKey, $columnValue);
            }
        }
        return $data;
    }

    private function setWidth($key, $value) {
        if (!isset($this->widths[$key])){
            $this->widths[$key] = 0;
        }
        $width =  (strlen($value) + mb_strlen($value,'UTF8')) / 2;
        if ($width > $this->widths[$key]){
            $this->widths[$key] = $width;
        }
    }

    private  function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = null) {
        if ($encoding === null){
            $encoding = mb_internal_encoding();
        }
        $diff = strlen($input) - (strlen($input) + mb_strlen($input,$encoding)) / 2;
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }

    private function setData($data)
    {
        if (!is_array($data)){
            $data = [];
        }
        $arrayData = [];
        foreach ($data as $row) {
            if (is_array($row)){
                $arrayData[] = $row;
            } else if (is_object($row)){
                $arrayData[] = get_object_vars($row);
            }
        }
        $this->data = $arrayData;
        return $this;
    }
}
