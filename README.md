
全新的基于Yii2的模板引擎，依据作者习惯，编写的一套独特语法的模板引擎(类似Vue的特性)

实例
for循环
```php
<div php-each="<?php foreach ([1,2,3,4] as $a) ?>">
        <?=$a; ?>
</div>
```

输出
```html
<div>
  1
</div>
<div>
  2
</div>
<div>
  3
</div>
<div>
  4
</div>
```


if语句
```php
<div php-if="<?php (1==2) ?>">
  test
</div>
```


嵌套
```php
<div php-if="<?php (1==2) ?>">
    <div php-each="<?php foreach ([1,2,3,4] as $a) ?>">
        <?=$a; ?>
    </div>
</div>
```


内联样式
```html
<style php-style>
  body{color:red}
</style>
```
模板自动调用  view::registerCss()


内联js脚本
```JavaScript
<script php-script php-script-pos="ready">
  alert(1);
</script>
```
模板自动调用  view::registerJs() 并且在 php-script-pos设定的位置执行
