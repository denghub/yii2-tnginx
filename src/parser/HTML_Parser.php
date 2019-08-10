<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 10:49 PM
 */

namespace tnginx\parser;

/**
 * Parses a HTML document into a HTML DOM
 */
class HTML_Parser extends HTML_Parser_Base
{
    /**
     * Root object
     * @internal If string, then it will create a new instance as root
     * @var HTML_Node
     */
    public $root = HTML_Node::class;
    /**
     * Current parsing hierarchy
     * @internal Root is always at index 0, current tag is at the end of the array
     * @var HTML_Node[]
     * @access private
     */
    public $hierarchy = array();
    /**
     * Tags that don't need closing tags
     * @var array
     * @access private
     */
    protected $tags_selfclose = array(
        'area' => true,
        'base' => true,
        'basefont' => true, // deprecated
        'bgsound' => true, // deprecated
        'br' => true,
        'col' => true,
        'command' => true,
        'embed' => true,
        'frame' => true, // deprecated
        'hr' => true,
        'img' => true,
        'input' => true,
        'ins' => true, // deprecated
        'isindex' => true, // deprecated
        'keygen' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'plaintext' => true, // deprecated
        'source' => true,
        'track' => true,
        'wbr' => true
    );
    /**
     * Class constructor
     * @param string $doc Document to be tokenized
     * @param int $pos Position to start parsing
     * @param HTML_Node $root Root node, null to auto create
     */
    public function __construct($doc = '', $pos = 0, $root = null)
    {
        if ($root === null) {
            $root = new $this->root('~root~', null);
        }
        $this->root = $root;
        parent::__construct($doc, $pos);
    }
    public function __destruct()
    {
        unset($this->root);
    }
    /**
     * Class magic invoke method, performs {@link select()}
     * @param string $query
     * @return HTML_Node|HTML_Node[]
     * @access private
     */
    public function __invoke($query = '*')
    {
        return $this->select($query);
    }
    /**
     * Class magic toString method, performs {@link HTML_Node::toString()}
     * @return string
     * @access private
     */
    public function __toString()
    {
        return $this->root->getInnerText();
    }
    /**
     * Performs a css select query on the root node
     * @param string $query
     * @param int|bool $index
     * @param bool $recursive
     * @param bool $check_self
     * @return HTML_Node|HTML_Node[]
     * @see HTML_Node::select()
     */
    public function select($query = '*', $index = false, $recursive = true, $check_self = false)
    {
        return $this->root->select($query, $index, $recursive, $check_self);
    }
    /**
     * Updates the current hierarchy status and checks for
     * correct opening/closing of tags
     * @param bool|null $self_close Is current tag self closing? Null to use {@link tags_selfclose}
     * @internal This is were most of the nodes get added
     * @access private
     */
    protected function parse_hierarchy($self_close = null)
    {
        if ($self_close === null) {
            $this->status['self_close'] = ($self_close = isset($this->tags_selfclose[strtolower($this->status['tag_name'])]));
        }
        if ($self_close) {
            if ($this->status['closing_tag']) {
                /** @var HTML_Node[] $c */
                $c = $this->hierarchy[count($this->hierarchy) - 1]->children;
                $found = false;
                for ($count = count($c), $i = $count - 1; $i >= 0; $i--) {
                    if (strcasecmp($c[$i]->tag, $this->status['tag_name']) === 0) {
                        for ($ii = $i + 1; $ii < $count; $ii++) {
                            $index = null; //Needs to be passed by ref
                            $c[$i + 1]->changeParent($c[$i], $index);
                        }
                        $c[$i]->self_close = false;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $this->addError('Closing tag "' . $this->status['tag_name'] . '" which is not open');
                }
            } elseif ($this->status['tag_name'][0] === '?') {
                $index = null; //Needs to be passed by ref
                $this->hierarchy[count($this->hierarchy) - 1]->addXML($this->status['tag_name'], '', $this->status['attributes'], $index);
            } elseif ($this->status['tag_name'][0] === '%') {
                $index = null; //Needs to be passed by ref
                $this->hierarchy[count($this->hierarchy) - 1]->addASP($this->status['tag_name'], '', $this->status['attributes'], $index);
            } else {
                $index = null; //Needs to be passed by ref
                $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
            }
        } elseif ($this->status['closing_tag']) {
            $found = false;
            for ($count = count($this->hierarchy), $i = $count - 1; $i >= 0; $i--) {
                if (strcasecmp($this->hierarchy[$i]->tag, $this->status['tag_name']) === 0) {
                    for ($ii = ($count - $i - 1); $ii >= 0; $ii--) {
                        $e = array_pop($this->hierarchy);
                        if ($ii > 0) {
                            $this->addError('Closing tag "' . $this->status['tag_name'] . '" while "' . $e->tag . '" is not closed yet');
                        }
                    }
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->addError('Closing tag "' . $this->status['tag_name'] . '" which is not open');
            }
        } else {
            $index = null; //Needs to be passed by ref
            $this->hierarchy[] = $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
        }
    }
    protected function parse_cdata()
    {
        if (!parent::parse_cdata()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $this->hierarchy[count($this->hierarchy) - 1]->addCDATA($this->status['cdata'], $index);
        return true;
    }
    protected function parse_comment()
    {
        if (!parent::parse_comment()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $this->hierarchy[count($this->hierarchy) - 1]->addComment($this->status['comment'], $index);
        return true;
    }
    protected function parse_conditional()
    {
        if (!parent::parse_conditional()) {
            return false;
        }
        if ($this->status['comment']) {
            $index = null; //Needs to be passed by ref
            $e = $this->hierarchy[count($this->hierarchy) - 1]->addConditional($this->status['tag_condition'], true, $index);
            if ($this->status['text'] !== '') {
                $index = null; //Needs to be passed by ref
                $e->addText($this->status['text'], $index);
            }
        } else {
            if ($this->status['closing_tag']) {
                $this->parse_hierarchy(false);
            } else {
                $index = null; //Needs to be passed by ref
                $this->hierarchy[] = $this->hierarchy[count($this->hierarchy) - 1]->addConditional($this->status['tag_condition'], false, $index);
            }
        }
        return true;
    }
    protected function parse_doctype()
    {
        if (!parent::parse_doctype()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $this->hierarchy[count($this->hierarchy) - 1]->addDoctype($this->status['dtd'], $index);
        return true;
    }
    protected function parse_php()
    {
        if (!parent::parse_php()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $this->hierarchy[count($this->hierarchy) - 1]->addXML('php', $this->status['text'], array(), $index);
        return true;
    }
    protected function parse_asp()
    {
        if (!parent::parse_asp()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $this->hierarchy[count($this->hierarchy) - 1]->addASP('', $this->status['text'], array(), $index);
        return true;
    }
    protected function parse_script()
    {
        if (!parent::parse_script()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $e = $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
        if ($this->status['text'] !== '') {
            $index = null; //Needs to be passed by ref
            $e->addText($this->status['text'], $index);
        }
        return true;
    }
    protected function parse_style()
    {
        if (!parent::parse_style()) {
            return false;
        }
        $index = null; //Needs to be passed by ref
        $e = $this->hierarchy[count($this->hierarchy) - 1]->addChild($this->status, $index);
        if ($this->status['text'] !== '') {
            $index = null; //Needs to be passed by ref
            $e->addText($this->status['text'], $index);
        }
        return true;
    }
    protected function parse_tag_default()
    {
        if (!parent::parse_tag_default()) {
            return false;
        }
        $this->parse_hierarchy(($this->status['self_close']) ? true : null);
        return true;
    }
    protected function parse_text()
    {
        parent::parse_text();
        if ($this->status['text'] !== '') {
            $index = null; //Needs to be passed by ref
            $this->hierarchy[count($this->hierarchy) - 1]->addText($this->status['text'], $index);
        }
    }
    public function parse_all()
    {
        $this->hierarchy = array($this->root);
        return ((parent::parse_all()) ? $this->root : false);
    }
}