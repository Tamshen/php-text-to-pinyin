<?php

namespace Overtrue\Pinyin;

use ArrayAccess;
use JsonSerializable;
use Stringable;


use InvalidArgumentException;

class Pinyin
{
    public static function name($name, $toneStyle = 'symbol')
    {
        return self::converter()->surname()->withToneStyle($toneStyle)->convert($name);
    }

    public static function passportName($name, $toneStyle = 'none')
    {
        return self::converter()->surname()->yuToYu()->withToneStyle($toneStyle)->convert($name);
    }

    public static function phrase($string, $toneStyle = 'symbol')
    {
        return self::converter()->noPunctuation()->withToneStyle($toneStyle)->convert($string);
    }

    public static function sentence($string, $toneStyle = 'symbol', $yuto= "v")
    {
        // return self::converter()->withToneStyle($toneStyle)->convert($string);
        if($yuto == "v"){
            return self::converter()->yuToV()->withToneStyle($toneStyle)->convert($string);
        }else if($yuto == "u"){
            return self::converter()->yuToU()->withToneStyle($toneStyle)->convert($string);
        }else{
            return self::converter()->yuToYu()->withToneStyle($toneStyle)->convert($string);
        }
    }

    public static function fullSentence($string, $toneStyle = 'symbol')
    {
        return self::converter()->noCleanup()->withToneStyle($toneStyle)->convert($string);
    }

    public static function heteronym($string, $toneStyle = 'symbol', $asList = false)
    {
        return self::converter()->heteronym($asList)->withToneStyle($toneStyle)->convert($string);
    }

    public static function heteronymAsList($string, $toneStyle = 'symbol')
    {
        return self::heteronym($string, $toneStyle, true);
    }

    public static function polyphones($string, $toneStyle = 'symbol', $asList = false)
    {
        return self::heteronym($string, $toneStyle, $asList);
    }

    public static function polyphonesAsArray($string, $toneStyle = 'symbol')
    {
        return self::heteronym($string, $toneStyle, true);
    }

    public static function chars($string, $toneStyle = 'symbol')
    {
        return self::converter()->onlyHans()->noWords()->withToneStyle($toneStyle)->convert($string);
    }

    public static function permalink($string, $delimiter = '-')
    {
        if (!in_array($delimiter, ['_', '-', '.', ''], true)) {
            throw new InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
        }

        return self::converter()->noPunctuation()->noTone()->convert($string)->join($delimiter);
    }

    public static function nameAbbr($string)
    {
        return self::abbr($string, true);
    }

    public static function abbr($string, $asName = false, $preserveEnglishWords = false)
    {
        return self::converter()->noTone()
            ->noPunctuation()
            ->when($asName, function ($c) {
                return $c->surname();
            })
            ->convert($string)
            ->map(function ($pinyin) use ($string, $preserveEnglishWords) {
                if ($preserveEnglishWords && strpos($string, $pinyin) !== false) {
                    return $pinyin;
                }

                return is_numeric($pinyin) || preg_match('/\d{2,}/', $pinyin) ? $pinyin : mb_substr($pinyin, 0, 1);
            });
    }

    public static function converter()
    {
        return Converter::make();
    }

    public static function __callStatic($name, $arguments)
    {
        $converter = self::converter();

        if (method_exists($converter, $name)) {
            return $converter->$name(...$arguments);
        }

        throw new InvalidArgumentException("Method {$name} does not exist.");
    }
}



class Collection implements ArrayAccess, JsonSerializable, Stringable
{
    protected $items = [];

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function join($separator = ' ')
    {
        return implode($separator, \array_map(function ($item) {
            return \is_array($item) ? '[' . \implode(', ', $item) . ']' : $item;
        }, $this->items));
    }

    public function map($callback)
    {
        return new static(array_map($callback, $this->all()));
    }

    public function all()
    {
        return $this->items;
    }

    public function toArray()
    {
        return $this->all();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->all(), $options);
    }

    public function __toString(): string
    {
        return $this->join();
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
}


class Converter
{
    private static $SEGMENTS_COUNT = 10;

    private static $WORDS_PATH = __DIR__ . '/data/words-%s.php';

    private static $CHARS_PATH = __DIR__ . '/data/chars.php';

    private static $SURNAMES_PATH = __DIR__ . '/data/surnames.php';

    public static $TONE_STYLE_SYMBOL = 'symbol';

    public static $TONE_STYLE_NUMBER = 'number';

    public static $TONE_STYLE_NONE = 'none';

    protected $heteronym = false;

    protected $heteronymAsList = false;

    protected $asSurname = false;

    protected $noWords = false;

    protected $cleanup = true;

    protected $yuTo = 'v';

    protected $toneStyle = 'symbol';

    protected $regexps = [
        'separator' => '\p{Z}',
        'mark'      => '\p{M}',
        'tab'       => "\t",
    ];
    
    public static $REGEXPS = [
        'number'      => '0-9',
        'alphabet'    => 'a-zA-Z',
        'hans'        => '\x{3007}\x{2E80}-\x{2FFF}\x{3100}-\x{312F}\x{31A0}-\x{31EF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}',
        'punctuation' => '\p{P}',
    ];

    public function __construct()
    {
        $this->regexps = array_merge($this->regexps, self::$REGEXPS);
    }

    public static function make()
    {
        return new static;
    }

    public function heteronym($asList = false)
    {
        $this->heteronym = true;
        $this->heteronymAsList = $asList;

        return $this;
    }

    public function polyphonic($asList = false)
    {
        return $this->heteronym($asList);
    }

    public function surname()
    {
        $this->asSurname = true;

        return $this;
    }

    public function noWords()
    {
        $this->noWords = true;

        return $this;
    }

    public function noCleanup()
    {
        $this->cleanup = false;

        return $this;
    }

    public function onlyHans()
    {
        $this->regexps['hans'] = self::$REGEXPS['hans'];

        return $this->noAlpha()->noNumber()->noPunctuation();
    }

    public function noAlpha()
    {
        unset($this->regexps['alphabet']);

        return $this;
    }

    public function noNumber()
    {
        unset($this->regexps['number']);

        return $this;
    }

    public function noPunctuation()
    {
        unset($this->regexps['punctuation']);

        return $this;
    }

    public function withToneStyle($toneStyle)
    {
        $this->toneStyle = $toneStyle;

        return $this;
    }

    public function noTone()
    {
        $this->toneStyle = self::$TONE_STYLE_NONE;

        return $this;
    }

    public function useNumberTone()
    {
        $this->toneStyle = self::$TONE_STYLE_NUMBER;

        return $this;
    }

    public function yuToYu()
    {

        $this->yuTo = 'yu';

        return $this;
    }

    public function yuToV()
    {

        $this->yuTo = 'v';

        return $this;
    }

    public function yuToU()
    {

        $this->yuTo = 'u';

        return $this;
    }

    // 回去
    

    public function when($condition, $callback)
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function convert($string, $beforeSplit = null)
    {
        $string = preg_replace_callback('~[a-z0-9_-]+~i', function ($matches) {
            return "\t" . $matches[0];
        }, $string);

        if ($this->cleanup) {
            $string = preg_replace(sprintf('~[^%s]~u', implode($this->regexps)), '', $string);
        }

        if ($this->heteronym) {
            return $this->convertAsChars($string, true);
        }

        if ($this->noWords) {
            return $this->convertAsChars($string);
        }

        if ($this->asSurname) {
            $string = $this->convertSurname($string);
        }

        for ($i = 0; $i < self::$SEGMENTS_COUNT; $i++) {
            $string = strtr($string, require sprintf(self::$WORDS_PATH, $i));
        }

        return $this->split($beforeSplit ? $beforeSplit($string) : $string);
    }

    public function convertAsChars($string, $polyphonic = false)
    {
        $map = require self::$CHARS_PATH;

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        $items = [];
        foreach ($chars as $char) {
            if (isset($map[$char])) {
                if ($polyphonic) {
                    $pinyin = array_map(function ($pinyin) {
                        return $this->formatTone($pinyin, $this->toneStyle);
                    }, $map[$char]);
                    if ($this->heteronymAsList) {
                        $items[] = [$char => $pinyin];
                    } else {
                        $items[$char] = $pinyin;
                    }

                } else {
                    $items[$char] = $this->formatTone($map[$char][0], $this->toneStyle);
                }
            }
        }

        return new Collection($items);
    }

    protected function convertSurname($name)
    {
        static $surnames = null;
        $surnames = $surnames ?: require self::$SURNAMES_PATH;

        foreach ($surnames as $surname => $pinyin) {
            if (strpos($name, $surname) === 0) {
                return $pinyin . mb_substr($name, mb_strlen($surname));
            }
        }

        return $name;
    }

    protected function split($item)
    {
        $items = array_values(array_filter(preg_split('/\s+/i', $item)));

        foreach ($items as $index => $item) {
            $items[$index] = $this->formatTone($item, $this->toneStyle);
        }

        return new Collection($items);
    }

    protected function formatTone($pinyin, $style)
    {
        if ($style === self::$TONE_STYLE_SYMBOL) {
            return $pinyin;
        }

        $replacements = [
            'ɑ'  => ['a', 5],
            'ü'  => ['v', 5],
            'üē' => ['ue', 1],
            'üé' => ['ue', 2],
            'üě' => ['ue', 3],
            'üè' => ['ue', 4],
            'ā'  => ['a', 1],
            'ē'  => ['e', 1],
            'ī'  => ['i', 1],
            'ō'  => ['o', 1],
            'ū'  => ['u', 1],
            'ǖ'  => ['v', 1],
            'á'  => ['a', 2],
            'é'  => ['e', 2],
            'í'  => ['i', 2],
            'ó'  => ['o', 2],
            'ú'  => ['u', 2],
            'ǘ'  => ['v', 2],
            'ǎ'  => ['a', 3],
            'ě'  => ['e', 3],
            'ǐ'  => ['i', 3],
            'ǒ'  => ['o', 3],
            'ǔ'  => ['u', 3],
            'ǚ'  => ['v', 3],
            'à'  => ['a', 4],
            'è'  => ['e', 4],
            'ì'  => ['i', 4],
            'ò'  => ['o', 4],
            'ù'  => ['u', 4],
            'ǜ'  => ['v', 4],
        ];

        foreach ($replacements as $unicode => $replacement) {
            if (strpos($pinyin, $unicode) !== false) {
                $umlaut = $replacement[0];

                if ($this->yuTo !== 'v' && $umlaut === 'v') {
                    $umlaut = $this->yuTo;
                }

                $pinyin = str_replace($unicode, $umlaut, $pinyin);

                if ($this->toneStyle === self::$TONE_STYLE_NUMBER) {
                    $pinyin .= $replacement[1];
                }
            }
        }

        return $pinyin;
    }
}
