<?php

// 引入自动加载文件
require dirname(__FILE__) . '/../autoload.php';

use Overtrue\Pinyin\Pinyin;




$CSS = '
<style>
    body{
        margin: 1rem;
        padding: 1rem;
    }
    H3{
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
        line-height: 1.2;
        margin: 1.5rem 0 1rem;
    }
    code {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-left: 3px solid #f36d33;
        color: #666;
        page-break-inside: avoid;
        font-family: monospace;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 1.6em;
        max-width: 100%;
        overflow: auto;
        padding: 1em 1.5em;
        display: block;
        word-wrap: break-word;
    }
</style>
';

echo $CSS;



echo "<h3>返回值</h3>";

$pinyin = Pinyin::sentence('你好，世界');
echo $pinyin; // nǐ hǎo shì jiè

// 直接将对象转成字符串
$string = (string) $pinyin; // nǐ hǎo shì jiè

$pinyin->toArray(); // ['nǐ', 'hǎo', 'shì', 'jiè']

echo "<br>";
echo "<code>";
print_r($pinyin->toArray()); // ['nǐ', 'hǎo', 'shì', 'jiè']
echo "</code>";

// 直接使用索引访问
echo $pinyin[0]; // 'nǐ'

echo "<code>";
// 使用函数遍历
print_r($pinyin->map('ucfirst')); // ['Nǐ', 'Hǎo', 'Shì', 'Jiè']
echo "</code>";

// 拼接为字符串
$pinyin->join(' '); // 'nǐ hǎo shì jiè'
$pinyin->join('-'); // 'nǐ-hǎo-shì-jiè'

// 转成 json
$pinyin->toJson(); // '["nǐ","hǎo","shì","jiè"]'
json_encode($pinyin); // '["nǐ","hǎo","shì","jiè"]'



echo "<h3>文字段落转拼音</h3>";


echo Pinyin::sentence('带着希望去旅行，比到达终点更美好');
// dài zhe xī wàng qù lǚ xíng ， bǐ dào dá zhōng diǎn gèng měi hǎo
echo "<br>";
// 去除声调
echo Pinyin::sentence('带着希望去旅行，比到达终点更美好', 'none');
// dai zhe xi wang qu lv xing ， bi dao da zhong dian geng mei hao
echo "<br>";
// 保留所有非汉字字符
echo Pinyin::fullSentence('ル是片假名，π是希腊字母', 'none');
// ル shi pian jia ming ，π shi xi la zi mu
echo "<br>";

echo "<h3>生成用于链接的拼音字符串</h3>";

echo Pinyin::permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lyu-xing
echo "<br>";
echo Pinyin::permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lyu.xing
echo "<br>";


echo "<h3>获取首字符字符串</h3>";

Pinyin::abbr('带着希望去旅行'); // ['d', 'z', 'x', 'w', 'q', 'l', 'x']
echo "<code>";
// 使用函数遍历
print_r(Pinyin::abbr('带着希望去旅行'));
echo "</code>";

echo Pinyin::abbr('带着希望去旅行')->join('-'); // d-z-x-w-q-l-x
echo "<br>";
echo Pinyin::abbr('你好2018！')->join(''); // nh2018
echo "<br>";
echo Pinyin::abbr('Happy New Year! 2018！')->join(''); // HNY2018
echo "<br>";
// 保留原字符串的英文单词
echo Pinyin::abbr('CGV电影院', false, true)->join(''); // CGVdyy
echo "<br>";



echo "<h3>姓名首字母</h3>";

Pinyin::nameAbbr('欧阳'); // ['o', 'y']
echo Pinyin::nameAbbr('单单单')->join('-'); // s-d-d
echo "<br>";


echo "<h3>姓名转换</h3>";

$name1 = Pinyin::name('单某某'); // ['shàn', 'mǒu', 'mǒu']
$name2 = Pinyin::name('单某某', 'none'); // ['shan', 'mou', 'mou']
$name3 = Pinyin::name('单某某', 'none')->join('-'); // shan-mou-mou

echo $name1;
echo "<br>";
echo $name2;
echo "<br>";
echo $name3;
echo "<br>";


echo "<h3>护照姓名转换</h3>";

$name1 = Pinyin::passportName('吕小布'); // ['lyu', 'xiao', 'bu']
$name2 = Pinyin::passportName('女小花'); // ['nyu', 'xiao', 'hua']
$name3 = Pinyin::passportName('律师'); // ['lyu', 'shi']

echo $name1;
echo "<br>";
echo $name2;
echo "<br>";
echo $name3;
echo "<br>";




echo "<h3>多音字</h3>";

$pinyin = Pinyin::polyphones('重庆');

echo "<code>";
print_r($pinyin);
print_r($pinyin->toArray());

echo "</code>";


echo "<h3>v/yu/ü 的问题</h3>";

echo Pinyin::sentence('旅行1');
// lǚ xíng
echo "<br>";

echo Pinyin::sentence('旅行2', 'none');
// lv xing
echo "<br>";

echo Pinyin::sentence('旅行3', 'none', "yu");
// lyu xing
echo "<br>";

echo Pinyin::sentence('旅行4', 'none', "u");
// lu xing
echo "<br>";

echo Pinyin::sentence('旅行5', 'none', "v");
// lv xing
echo "<br>";
