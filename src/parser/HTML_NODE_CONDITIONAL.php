<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:47 PM
 */

namespace tnginx\parser;


/**
 * Node subclass for conditional tags
 */
class HTML_NODE_CONDITIONAL extends HTML_Node
{
    const NODE_TYPE = self::NODE_CONDITIONAL;
    public $tag = '~conditional~';
    /**
     * @var string
     */
    public $condition = '';
    /**
     * Class constructor
     * @param HTML_Node $parent
     * @param string $condition e.g. "if IE"
     * @param bool $hidden <!--[if if true, <![if if false
     */
    public function __construct($parent, $condition = '', $hidden = true)
    {
        $this->parent = $parent;
        $this->hidden = $hidden;
        $this->condition = $condition;
    }
    protected function filter_element()
    {
        return false;
    }
    protected function toString_attributes()
    {
        return '';
    }
    /**
     * Returns the node as string
     * @param bool $attributes Print attributes (of child tags)
     * @param bool|int $recursive How many sublevels of childtags to print. True for all.
     * @param bool|int $content_only Only print text, false will print tags too.
     * @return string
     */
    public function toString($attributes = true, $recursive = true, $content_only = false)
    {
        if ($content_only) {
            if (is_int($content_only)) {
                --$content_only;
            }
            return $this->toString_content($attributes, $recursive, $content_only);
        }
        $s = '<!' . (($this->hidden) ? '--' : '') . '[' . $this->condition . ']>';
        if ($recursive) {
            $s .= $this->toString_content($attributes);
        }
        $s .= '<![endif]' . (($this->hidden) ? '--' : '') . '>';
        return $s;
    }
}