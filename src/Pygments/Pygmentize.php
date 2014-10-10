<?php

namespace Pygments;

/**
 * Class Pygmentize
 * @package Pygments
 */
class Pygmentize
{

    const EXIT_SUCCESS = 0;

    private static $bin = null;

    public static function version()
    {
        $return = self::exec('-V');
        $res = preg_match(
            '/Pygments version ([^,]*), \(c\) (?:.*)/i',
            $return,
            $matches
        );
        if($res) {
            return $matches[1];
        } else {
            return $return;
        }
    }

    public static function lexers()
    {
        $return = self::exec('-L lexers');
        preg_match_all('/\* (.*):(?:\s*)([^\(\*\n]*)/', $return, $matches);
        $lexers = array();
        foreach($matches[1] as $k=>$match) {
            $list = explode(', ', $match);
            foreach($list as $e) {
                $lexers[$e] = $matches[2][$k];
            }
        }
        return $lexers;
    }

    public static function styles()
    {
        $return = self::exec('-L styles');
        preg_match_all('/\* (.*):(?:\s*)([^\n]*)/', $return, $matches);
        $styles = array();
        foreach($matches[1] as $k=>$match) {
            $styles[$match] = $matches[2][$k];
        }
        return $styles;
    }

    public static function getStyle($style)
    {
        if(!array_key_exists($style, self::styles())) {
            throw new StyleException('Style not supported: ' . $style);
        }
        return self::exec('-f html -S ' . $style);
    }

    public static function format($source, $lexer)
    {
        if(!array_key_exists($lexer, self::lexers())) {
            throw new LexerException('Lexer not supported: ' . $lexer);
        }
        $format = self::exec('-f html -l ' . $lexer, $source);
        preg_match('#<div class="highlight"><pre>(.*)\s</pre></div>#s', $format, $matches);
        return $matches[1];
    }

    /**
     * @param $options
     * @param string $pipe
     * @throws FormatException
     * @throws \Exception
     * @return string
     */
    private static function exec($options, $pipe = null)
    {
        $cmd = $pipe ? 'echo ' . escapeshellarg($pipe) . ' | ' : '';
        $cmd .= self::getBin() . ' ' . $options . ' 2>&1';
        exec($cmd, $output, $returnVar);
        $output = implode(PHP_EOL, $output);
        if ($returnVar == self::EXIT_SUCCESS) {
            return $output;
        }
        $error = self::parseError($output);
        if($error == 'format') {
            throw new FormatException($output);
        } else {
            throw new \Exception($output);
        }
    }

    private static function parseError($output)
    {
        if(preg_match("/Error: No formatter found for name '(.*)'/", $output)) {
            return 'format';
        } else {
            return 'error';
        }
    }

    private static function getBin()
    {
        if(self::$bin == null) {
            $returnVal = shell_exec("which pygmentize");
            if(empty($returnVal)) {
                throw new BinaryNotFoundException("Pygmentize not found.");
            }
            self::setBin(trim($returnVal));
        }
        return self::$bin;
    }

    private static function setBin($bin)
    {
        self::$bin = $bin;
    }
}


