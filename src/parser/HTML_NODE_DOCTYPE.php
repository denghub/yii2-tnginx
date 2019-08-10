<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:47 PM
 */

namespace tnginx\parser;



/**
 * Node subclass for doctype tags
 */
class HTML_NODE_DOCTYPE extends HTML_Node
{
    const NODE_TYPE = self::NODE_DOCTYPE;
    public $tag = '!DOCTYPE';
    /**
     * @var string
     */
    public $dtd = '';
    /**
     * Class constructor
     * @param HTML_Node $parent
     * @param string $dtd
     */
    public function __construct($parent, $dtd = '')
    {
        $this->parent = $parent;
        $this->dtd = $dtd;
    }
    protected function filter_element()
    {
        return false;
    }
    protected function toString_attributes()
    {
        return '';
    }
    protected function toString_content($attributes = true, $recursive = true, $content_only = false)
    {
        return $this->text;
    }
    public function toString($attributes = true, $recursive = true, $content_only = false)
    {
        return '<' . $this->tag . ' ' . $this->dtd . '>';
    }
}