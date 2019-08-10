<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:47 PM
 */

namespace tnginx\parser;


/**
 * Node subclass for CDATA tags
 */
class HTML_NODE_CDATA extends HTML_Node
{
    const NODE_TYPE = self::NODE_CDATA;
    public $tag = '~cdata~';
    /**
     * @var string
     */
    public $text = '';
    /**
     * Class constructor
     * @param HTML_Node $parent
     * @param string $text
     */
    public function __construct($parent, $text = '')
    {
        $this->parent = $parent;
        $this->text = $text;
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
        return '<![CDATA[' . $this->text . ']]>';
    }
}