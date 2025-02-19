# php-Text2Pinyin

说明：composer 改为手动引入，方便嵌入+低版本兼容

原项目地址：https://github.com/overtrue/pinyin

性能测试：
访问：/benchmark/index.php

建议：正式环境删除benchmark文件夹和tests文件夹

调用更改：
 - v/yu/ü 的调用，改为sentence增加第三个传入参数，可选值为：`v`、`yu`、`ü`，默认为 `v`，例如：`Pinyin::sentence('旅行', 'none', 'yu')`


## 安装

1. 下载文件包+解压 [点我下载](https://codeload.github.com/Tamshen/php-text-to-pinyin/zip/refs/heads/master)


2. 丢到项目目录
举例目录树：
```
│   │   ...
│   ├── php-Text2Pinyin
├── index.php
```

3.  引入文件
```php
// index.php 
<?php
require_once dirname(__FILE__) . '/php-text-to-pinyin-master/autoload.php';
use Overtrue\Pinyin\Pinyin;

// 默认
$pinyin = Pinyin::sentence('你好，世界');
echo $pinyin; // nǐ hǎo shì jiè

```



## 使用

### 拼音风格

除了获取首字母的方法外，所有方法都支持第二个参数，用于指定拼音的格式，可选值为：

- `symbol` （默认）声调符号，例如 `pīn yīn`
- `none` 不输出拼音，例如 `pin yin`
- `number` 末尾数字模式的拼音，例如 `pin1 yin1`

### 返回值

```php
use Overtrue\Pinyin\Pinyin;

$pinyin = Pinyin::sentence('你好，世界');
```

你可以通过以下方式访问集合内容:

```php
echo $pinyin; // nǐ hǎo shì jiè

// 直接将对象转成字符串
$string = (string) $pinyin; // nǐ hǎo shì jiè

$pinyin->toArray(); // ['nǐ', 'hǎo', 'shì', 'jiè']

// 直接使用索引访问
$pinyin[0]; // 'nǐ'

// 使用函数遍历
$pinyin->map('ucfirst'); // ['Nǐ', 'Hǎo', 'Shì', 'Jiè']

// 拼接为字符串
$pinyin->join(' '); // 'nǐ hǎo shì jiè'
$pinyin->join('-'); // 'nǐ-hǎo-shì-jiè'

// 转成 json
$pinyin->toJson(); // '["nǐ","hǎo","shì","jiè"]'
json_encode($pinyin); // '["nǐ","hǎo","shì","jiè"]'
```

### 文字段落转拼音

```php
use Overtrue\Pinyin\Pinyin;

echo Pinyin::sentence('带着希望去旅行，比到达终点更美好');
// dài zhe xī wàng qù lǚ xíng ， bǐ dào dá zhōng diǎn gèng měi hǎo

// 去除声调
echo Pinyin::sentence('带着希望去旅行，比到达终点更美好', 'none');
// dai zhe xi wang qu lv xing ， bi dao da zhong dian geng mei hao

// 保留所有非汉字字符
echo Pinyin::fullSentence('ル是片假名，π是希腊字母', 'none');
// ル shi pian jia ming ，π shi xi la zi mu
```

### 生成用于链接的拼音字符串

通常用于文章链接等，可以使用 `permalink` 方法获取拼音字符串：

```php
echo Pinyin::permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lyu-xing
echo Pinyin::permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lyu.xing
```

### 获取首字符字符串

通常用于创建搜索用的索引，可以使用 `abbr` 方法转换：

```php
Pinyin::abbr('带着希望去旅行'); // ['d', 'z', 'x', 'w', 'q', 'l', 'x']
echo Pinyin::abbr('带着希望去旅行')->join('-'); // d-z-x-w-q-l-x
echo Pinyin::abbr('你好2018！')->join(''); // nh2018
echo Pinyin::abbr('Happy New Year! 2018！')->join(''); // HNY2018

// 保留原字符串的英文单词
echo Pinyin::abbr('CGV电影院', false, true)->join(''); // CGVdyy
```

**姓名首字母**

将首字作为姓氏转换，其余作为普通词语转换：

```php
Pinyin::nameAbbr('欧阳'); // ['o', 'y']
echo Pinyin::nameAbbr('单单单')->join('-'); // s-d-d
```

### 姓名转换

姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 `dan`，而作为姓的时候读 `shan`。

```php
Pinyin::name('单某某'); // ['shàn', 'mǒu', 'mǒu']
Pinyin::name('单某某', 'none'); // ['shan', 'mou', 'mou']
Pinyin::name('单某某', 'none')->join('-'); // shan-mou-mou
```

### 护照姓名转换

根据国家规定 [关于中国护照旅行证上姓名拼音 ü（吕、律、闾、绿、女等）统一拼写为 YU 的提醒](http://sg.china-embassy.gov.cn/lsfw/zghz1/hzzxdt/201501/t20150122_2022198.htm) 的规则，将 `ü` 转换为 `yu`：

```php
Pinyin::passportName('吕小布'); // ['lyu', 'xiao', 'bu']
Pinyin::passportName('女小花'); // ['nyu', 'xiao', 'hua']
Pinyin::passportName('律师'); // ['lyu', 'shi']
```

### 多音字

多音字的返回值为关联数组的集合，默认返回去重后的所有读音：

```php
$pinyin = Pinyin::polyphones('重庆');

$pinyin['重']; // ["zhòng", "chóng", "tóng"]
$pinyin['庆']; // ["qìng"]

$pinyin->toArray();
// [
//     "重": ["zhòng", "chóng", "tóng"],
//     "庆": ["qìng"]
// ]
```

如果不想要去重，可以数组形式返回：

```php
$pinyin = Pinyin::polyphones('重庆重庆', Converter::TONE_STYLE_SYMBOL, true);

// or 
$pinyin = Pinyin::polyphonesAsArray('重庆重庆', Converter::TONE_STYLE_SYMBOL);

$pinyin->toArray();
// [
//     ["重" => ["zhòng", "chóng", "tóng"]],
//     ["庆" => ["qìng"]],
//     ["重" => ["zhòng", "chóng", "tóng"]],
//     ["庆" => ["qìng"]]
// ]
```

### 单字转拼音

和多音字类似，单字的返回值为字符串，多音字将根据该字字频调整得到常用音：

```php
$pinyin = Pinyin::chars('重庆');

echo $pinyin['重']; // "zhòng"
echo $pinyin['庆']; // "qìng"

$pinyin->toArray();
// [
//     "重": "zhòng",
//     "庆": "qìng"
// ]
```

> **Warning**
>
> 当单字处理时由于多音字来自词频表中取得常用音，所以在词语环境下可能出现不正确的情况，建议使用多音字处理。


## v/yu/ü 的问题

根据国家语言文字工作委员会的规定，`lv`、`lyu`、`lǚ` 都是正确的，但是 `lv` 是最常用的，所以默认使用 `lv`，如果你需要使用其他的，可以在初始化时传入：

```php
echo Pinyin::sentence('旅行2', 'none');
// lv xing
echo "<br>";

echo Pinyin::sentence('旅行3', 'none',"yu");
// lyu xing
echo "<br>";

echo Pinyin::sentence('旅行4', 'none',"u");
// lu xing
echo "<br>";

echo Pinyin::sentence('旅行5', 'none',"v");
// lv xing
echo "<br>";
```

> **Warning**
>
> 仅在拼音风格为非 `none` 模式下有效。



