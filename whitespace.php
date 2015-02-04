<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("lexer/lexer.php");
require_once("parser/parser.php");
require_once("parser/parser_list.php");

//use \lexer\Lexer;
//use \parser\Parser;

class Interpreter {

    protected $stack = [];
    protected $heap = [];
    protected $func_table = [];

    function __construct() {
        $this->stack = [];
        $this->heap = [];
        $this->func_table = [];
    }

    function execute_file($filename) {
        $content = file_get_contents($filename);
        $this->execute($content);
    }

    function executeFile($filename) {
        $content = file_get_contents($filename);
        $this->exec($content);
    }

    function execute($content) {
        $lexer = new Lexer($content);
        $parser = new Parser($lexer);
        $ast = $parser->parse();
        $this->pre($ast);
        $this->eval_ast($ast);
    }

    function exec($content) {
        $lexer = new Lexer($content);
        $parser = new ParserList($lexer);
        list($ast, $labels, $offsets) = $parser->parse();
        $this->evalList($ast, $labels);
    }

    function compile2File($filename, $filepath, $text = false) {
        $content = file_get_contents($filename);
        $ret = $text ? $this->toText($content) : $this->toBinFile($content);
        $fp = fopen($filepath, "wb");
        fwrite($fp, $ret);
    }

    function toText($content) {
        $lexer = new Lexer($content);
        $parser = new ParserList($lexer);
        list($ast, $labels, $offsets) = $parser->parse();
        $result = "";
        foreach ($ast as $token) {
            if (sizeof($token) == 2) {
                $result .= $lexer->getCmdName($token[0]) . " " . $token[1] . "\n";
            } else {
                $result .= $lexer->getCmdName($token[0]) . "\n";
            }
        }
        return $result;
    }

    function toBinFile($content) {
        $lexer = new Lexer($content);
        $parser = new ParserList($lexer);
        list($ast, $labels, $offsets) = $parser->parse();
        $result = "";
        foreach ($ast as $token) {
            if (sizeof($token) == 2) {
                if ($lexer->hasLabel($token)) {
                    $result .= pack("ll", $token[0], $offsets[$token[0]]);
                } else {
                    $result .= pack("ll", $token[0], $token[1]);
                }
            } else {
                $result .= pack("l", $token[0]);
            }
        }
        return $result;
    }

    function pre($ast) {
        $tmp = $ast;
        while (!empty($tmp)) {
            $token = $tmp["left"];
            switch ($token[0]) {
                case MARK:
                    $this->func_table[$token[1]] = $tmp["right"];
                    break;
                case ENDFUNC:

                    break;
            }
            $tmp = $tmp['right'];
        }
    }

    function eval_ast($ast, $func_call = false) {
        $tmp = $ast;
        while (!empty($tmp)) {
            $token = $tmp["left"];
            switch ($token[0]) {
                case PUSH:
                    echo "push:" . $token[1] . "\n";
                    array_push($this->stack, $token[1]);
                    break;
                case COPY:
                    array_push($this->stack, $this->stack[sizeof($this->stack) - 1]);
                    break;
                case COPYN:
                    array_push($this->stack, $this->stack[sizeof($this->stack) - 1 - $token[1]]);
                    break;
                case DROP:
                    array_pop($this->stack);
                    break;
                case EXCHANGE:
                    $len = sizeof($this->stack) - 1;
                    $tmp = $this->stack[$len];
                    $this->stack[$len] = $this->stack[$len - 1];
                    $this->stack[$len - 1] = $tmp;
                    break;
                case SLIDEOFF:
                    unset($this->stack[$token[1]]);
                    $this->stack = array_values($this->stack);
                    break;
                case ADD:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left + $right);
                    break;
                case SUB:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left - $right);
                    break;
                case MUL:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left * $right);
                    break;
                case DIV:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, intval($left / $right));
                    break;
                case MOD:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left % $right);
                    break;
                case STORE:
                    $len = sizeof($this->stack) - 1;
                    $value = $this->stack[$len];
                    $addr = $this->stack[$len - 1];
                    echo "store:" . $value . " to " . $addr . "\n";
                    $this->heap[$addr] = $value;
                    break;
                case READ:
                    $len = sizeof($this->stack) - 1;
                    $addr = $this->stack[$len];
//                    $addr = array_pop($this->stack);
//                    var_dump($this->stack);
//                    var_dump($this->heap);
                    echo "read:" . $this->heap[$addr] . " from " . $addr . "\n";
                    array_push($this->stack, $this->heap[$addr]);
                    break;
                case MARK:
                    $this->func_table[$token[1]] = $tmp["right"];
                    break;
                case CALL:
                    $this->eval_ast($this->func_table[$token[1]], true);
                    break;
                case JUMP:
                    $tmp = $this->func_table[$token[1]];
                    break;
                case JUMPNULL:
                    $len = sizeof($this->stack) - 1;
                    if ($this->stack[$len] == 0) {
                        $tmp = $this->func_table[$token[1]];
                    }
                    break;
                case JUMPDE:
                    $len = sizeof($this->stack) - 1;
                    if ($this->stack[$len] < 0) {
                        $tmp = $this->func_table[$token[1]];
                    }
                    break;
                case ENDFUNC:
                    if ($func_call) {
                        return;
                    }
                    break;
                case ENDLE:
                    exit(0);
                    break;
                case OUTCHAR:
                    $len = sizeof($this->stack) - 1;
                    echo chr($this->stack[$len]);
                    break;
                case OUTNUM:
                    $len = sizeof($this->stack) - 1;
                    echo $this->stack[$len];
                    break;
                case INCHAR:
                    $len = sizeof($this->stack) - 1;
                    $c = fgetc(STDIN);
                    if ($c == "\r") {
                        continue;
                    }
                    $addr = $this->stack[$len];
                    $this->heap[$addr] = ord($c);
                    break;
                case INNUM:
                    $len = sizeof($this->stack) - 1;
                    $c = fgets(STDIN);
                    $addr = $this->stack[$len];
                    $this->heap[$addr] = intval($c);
                    break;
            }
            $tmp = $tmp['right'];
        }
    }

    function evalList($ast, $labels, $func_call = false, $index = 0) {
        $ast_len = sizeof($ast);
        for ($i = $index; $i < $ast_len; ++$i) {
            $len = sizeof($this->stack) - 1;
//            echo "stack top: ".$this->stack[$len]. " \n";
//            echo $i ." ".$len. " ";
            $token = $ast[$i];
//            echo ' '.$token[0];
            switch ($token[0]) {
                case PUSH:
//                    echo "push:" . $token[1] . "\n";
                    array_push($this->stack, $token[1]);
                    break;
                case COPY:
//                    echo "copy:" . $this->stack[sizeof($this->stack) - 1] . "\n";
                    array_push($this->stack, $this->stack[sizeof($this->stack) - 1]);
                    break;
                case COPYN:
                    array_push($this->stack, $this->stack[sizeof($this->stack) - 1 - $token[1]]);
                    break;
                case DROP:
                    array_pop($this->stack);
                    break;
                case EXCHANGE:
                    $len = sizeof($this->stack) - 1;
                    $tmp = $this->stack[$len];
                    $this->stack[$len] = $this->stack[$len - 1];
                    $this->stack[$len - 1] = $tmp;
                    break;
                case SLIDEOFF:
                    unset($this->stack[$token[1]]);
                    $this->stack = array_values($this->stack);
                    break;
                case ADD:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left + $right);
                    break;
                case SUB:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left - $right);
                    break;
                case MUL:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left * $right);
                    break;
                case DIV:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, intval($left / $right));
                    break;
                case MOD:
                    $right = array_pop($this->stack);
                    $left = array_pop($this->stack);
                    array_push($this->stack, $left % $right);
                    break;
                case STORE:
//                    $len = sizeof($this->stack) - 1;
//                    $value = $this->stack[$len];
//                    $addr = $this->stack[$len - 1];
                    $value = array_pop($this->stack);
                    $addr = array_pop($this->stack);
//                    echo "store:" . $value . " to " . $addr . "\n";
                    $this->heap[$addr] = $value;
                    break;
                case READ:
//                    $len = sizeof($this->stack) - 1;
//                    $addr = $this->stack[$len];
//                    echo "read:" . $this->heap[$addr] . " from " . $addr . "\n";
                    $addr = array_pop($this->stack);
                    array_push($this->stack, $this->heap[$addr]);
                    break;
                case MARK:
//                    $this->func_table[$token[1]] = $tmp["right"];
                    break;
                case CALL:
                    $this->evalList($ast, $labels, true, $labels[$token[1]]);
                    break;
                case JUMP:
                    $i = $labels[$token[1]];
                    break;
                case JUMPNULL:
//                    var_dump($this->stack);
//                    $len = sizeof($this->stack) - 1;
                    $sign = array_pop($this->stack);
                    if ($sign == 0) {
                        $i = $labels[$token[1]];
                    }
                    break;
                case JUMPDE:
//                    $len = sizeof($this->stack) - 1;
                    $sign = array_pop($this->stack);
                    if ($sign < 0) {
                        $i = $labels[$token[1]];
                    }
                    break;
                case ENDFUNC:
//                    echo 'xxx';
                    if ($func_call) {
                        return;
                    }
                    break;
                case ENDLE:
//                    echo 'yyy';
                    exit(0);
                    break;
                case OUTCHAR:
//                    var_dump($this->stack);
//                    $len = sizeof($this->stack) - 1;
//                    $addr = array_pop($this->stack);
//                    echo chr($this->stack[$len]);
                    $c = array_pop($this->stack);
                    echo chr($c);
                    break;
                case OUTNUM:
//                    $len = sizeof($this->stack) - 1;
//                    echo $this->stack[$len];
                    $n = array_pop($this->stack);
                    echo $n;
                    break;
                case INCHAR:
//                    $len = sizeof($this->stack) - 1;
                    $c = fgetc(STDIN);
                    if ($c == "\r") {
                        continue;
                    }
//                    $addr = $this->stack[$len];
                    $addr = array_pop($this->stack);
                    $this->heap[$addr] = ord($c);
                    break;
                case INNUM:
                    $addr = array_pop($this->stack);
//                    $len = sizeof($this->stack) - 1;
                    $c = fgets(STDIN);
//                    $addr = $this->stack[$len];
                    $this->heap[$addr] = intval($c);
                    break;
            }
        }
    }

}

$interp = new Interpreter();
//$interp->execute_file($argv[1]);
//$interp->executeFile($argv[1]);
$interp->compile2File($argv[1],$argv[2]);
