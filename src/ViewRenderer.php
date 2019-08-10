<?php
/**
 * Created by PhpStorm.
 * User: wangning
 * Date: 2019/5/22
 * Time: 12:09 AM
 */

namespace tnginx;

use core\web\View;
use core\Yii;
use tnginx\parser\HtmlParser;

class ViewRenderer extends \core\base\ViewRenderer
{
    public $cachePath = '@runtime/TNginx/cache';

    protected $_dom;
    protected $_viewFile;

    public $commandMap=[
        'each'=>'php-each',
        'if'=>'php-if',
        'style'=>'php-style',
        'script'=>'php-script'
    ];

    public function init()
    {
        $_cachePath=Yii::getAlias($this->cachePath);
        if(!is_dir( $_cachePath)){
            mkdir( $_cachePath,0777,true);
        }
    }

    protected $_content;

    protected function _needParser(){
        $cFile=$this->_compileViewFile();
        if(!is_file($cFile)) return true;
        $ctime=filemtime($cFile);
        $vtime=filemtime($this->_viewFile);
        if($vtime>$ctime){
            return true;
        }
        return false;
    }

    protected function _flushContent(){
        $this->_dom=HtmlParser::str_get_dom($this->_content);
        $this->_content=$this->_dom->html();
    }

    protected function _parserEach(){
        $query=$this->_dom;
        $elements=$query('*['.$this->commandMap['each'].']');
        if(count($elements)==0) return false;
        foreach ($elements as $element){
            $srcContent=$element->html();
            $eachAttributeVal=$element->attributes[$this->commandMap['each']];
            if(!preg_match('/(foreach[ ]?\([ ]?(.*?)[ ]?as.*?\))/is',$eachAttributeVal,$m)){
                continue;
            }
            $element->deleteAttribute($this->commandMap['each']);
            $newContent=$element->html();
            $newContent='
                <?php '.$m[1].': ?>
                    '.$newContent.'
                <?php endforeach; ?>
            ';
            if($m[2]{0}=='$'){
                $newContent='
                    <?php if(!empty('.$m[2].')): ?>
                        '.$newContent.'
                    <?php endif; ?>
                ';
            }
            $this->_content=str_replace($srcContent,$newContent,$this->_content);
            $this->_flushContent();
        }
        return true;
    }

    protected function _parserStyle(){
        if(!preg_match_all('/<style.*?'.$this->commandMap['style'].'\="'.$this->commandMap['style'].'".*?>(.*?)<\/style>/is',$this->_content,$m)){
            return false;
        }
        if(empty($m[0])) return false;
        foreach ($m[0] as $k=>$style){
            $this->_content=str_replace($style,'
            <?php \core\base\ObContent::begin(); ?>
                '.$style.'
            <?php \core\Yii::$app->view->registerCss(\core\base\ObContent::end(\core\base\ObContent::TYPE_CSS)); ?>
            ',$this->_content);
            $this->_flushContent();
        }
        return true;
    }

    protected function _parserScript(){
        if(!preg_match_all('/<script.*?'.$this->commandMap['script'].'\="'.$this->commandMap['script'].'".*?>(.*?)<\/script>/is',$this->_content,$m)){
            return false;
        }
        if(empty($m[0])) return false;
        foreach ($m[0] as $k=>$script){
            $pos=View::POS_READY;
            if(preg_match('/php-script-pos="(.*?)"/',$script,$_m)){
                switch ($_m[1]){
                    case 'ready':
                        $pos=View::POS_READY;
                        break;
                    case 'begin':
                        $pos=View::POS_BEGIN;
                        break;
                    case 'end':
                        $pos=View::POS_END;
                        break;
                    case 'load':
                        $pos=View::POS_LOAD;
                        break;
                    case 'head':
                        $pos=View::POS_HEAD;
                        break;
                }
            }
            $this->_content=str_replace($script,'
            <?php \core\base\ObContent::begin(); ?>
                '.$script.'
            <?php \core\Yii::$app->view->registerJs(\core\base\ObContent::end(\core\base\ObContent::TYPE_JS),"'.$pos.'"); ?>
            ',$this->_content);
            $this->_flushContent();
        }
        return true;
    }

    protected function _parserIf(){

        $query=$this->_dom;
        $elements=$query('*['.$this->commandMap['if'].']');

        if(count($elements)==0) return false;

        foreach ($elements as $element){
            $srcContent=$element->html();
            $ifAttributeVal=$element->attributes[$this->commandMap['if']];
            if(!preg_match('/\<\?php(.*?)\?\>/',$ifAttributeVal,$m)){
                continue;
            }
            $ifAttributeVal=$m[1];
            $element->deleteAttribute($this->commandMap['if']);
            $newContent=$element->html();
            $newContent='
                <?php if('.$ifAttributeVal.'): ?>
                    '.$newContent.'
                <?php endif; ?>
            ';
            $this->_content=str_replace($srcContent,$newContent,$this->_content);
            $this->_flushContent();
        }

        return true;
    }

    protected function _parser(){
        $this->_dom=HtmlParser::str_get_dom(file_get_contents($this->_viewFile));
        $this->_content=$this->_dom->html();

        if(!$this->_needParser()){
            return true;
        }
        $hasParser=[
            $this->_parserEach(),
            $this->_parserIf(),
            $this->_parserStyle(),
            $this->_parserScript()
        ];

        return $this->_compile();
    }

    protected function _compile(){
        $file=$this->_compileViewFile();
        file_put_contents($file,$this->_content);
        return true;
    }

    protected function _compileViewFile(){
        return Yii::getAlias($this->cachePath).'/'.md5($this->_viewFile).'.php';
    }

    /**
     * Renders a view file.
     *
     * This method is invoked by [[View]] whenever it tries to render a view.
     * Child classes must implement this method to render the given view file.
     *
     * @param View $view the view object used for rendering the file.
     * @param string $file the view file.
     * @param array $params the parameters to be passed to the view file.
     * @return string the rendering result
     */
    public function render($view, $file, $params)
    {
        try{
            $this->_viewFile=$file;
            if($this->_parser()){
                return $view->renderPhpFile($this->_compileViewFile(),$params);
            }

        }catch (\Exception $e){
            return $view->renderPhpFile($file,$params);
        }
        return $view->renderPhpFile($file,$params);
    }

}