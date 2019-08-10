<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:46 PM
 */

namespace tnginx\parser;


/**
 * Node subclass for comments
 */
class HTML_NODE_COMMENT extends HTML_Node
{
    const NODE_TYPE = self::NODE_COMMENT;
    public $tag = '~comment~';
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
    public function isComment()
    {
        return true;
    }
    public function isTextOrComment()
    {
        return true;
    }
    protected function filter_element()
    {
        return false;
    }
    protected function filter_comment()
    {
        return true;
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
        return '<!--' . $this->text . '-->';
    }
}