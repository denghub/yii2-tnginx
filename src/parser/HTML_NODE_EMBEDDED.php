<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:47 PM
 */

namespace tnginx\parser;


/**
 * Node subclass for embedded tags like xml, php and asp
 */
class HTML_NODE_EMBEDDED extends HTML_Node
{
    /**
     * @var string
     * @internal specific char for tags, like ? for php and % for asp
     * @access private
     */
    public $tag_char = '';
    /**
     * @var string
     */
    public $text = '';
    /**
     * Class constructor
     * @param HTML_Node $parent
     * @param string $tag_char {@link $tag_char}
     * @param string $tag {@link $tag}
     * @param string $text
     * @param array $attributes array('attr' => 'val')
     */
    public function __construct($parent, $tag_char = '', $tag = '', $text = '', $attributes = array())
    {
        $this->parent = $parent;
        $this->tag_char = $tag_char;
        if ($tag[0] !== $this->tag_char) {
            $tag = $this->tag_char . $tag;
        }
        $this->tag = $tag;
        $this->text = $text;
        $this->attributes = $attributes;
        $this->self_close_str = $tag_char;
    }
    protected function filter_element()
    {
        return false;
    }
    public function toString($attributes = true, $recursive = true, $content_only = false)
    {
        $s = '<' . $this->tag;
        if ($attributes) {
            $s .= $this->toString_attributes();
        }
        $s .= $this->text . $this->self_close_str . '>';
        return $s;
    }
}