<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:48 PM
 */

namespace tnginx\parser;


/**
 * Node subclass for "?" tags, like php and xml
 */
class HTML_NODE_XML extends HTML_NODE_EMBEDDED
{
    const NODE_TYPE = self::NODE_XML;
    /**
     * Class constructor
     * @param HTML_Node $parent
     * @param string $tag {@link $tag}
     * @param string $text
     * @param array $attributes array('attr' => 'val')
     */
    public function __construct($parent, $tag = 'xml', $text = '', $attributes = array())
    {
        parent::__construct($parent, '?', $tag, $text, $attributes);
    }
}