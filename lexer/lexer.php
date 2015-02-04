<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace lexer;

        const PUSH = 1;
        const COPY = 2;
        const COPYN = 3;
        const EXCHANGE = 4;
        const DROP = 5;
        const SLIDEOFF = 6;
        const ADD = 7;
        const SUB = 8;
        const MUL = 9;
        const DIV = 10;
        const MOD = 11;
        const STORE = 12;
        const READ = 13;
        const MARK = 14;
        const CALL = 15;
        const JUMP = 16;
        const JUMPNULL = 17;
        const JUMPDE = 18;
        const ENDFUNC = 19;
        const ENDLE = 20;
        const OUTCHAR = 21;
        const OUTNUM = 22;
        const INCHAR = 23;
        const INNUM = 24;

class Lexer {

    protected $str = "";
    protected $index = 0;
    protected $inval = [];
    protected $token_table = [];

    function __construct($str) {
        $this->str = $str;
        $this->index = 0;
        $this->inval = [' ', "\n", "\t"];
        $this->token_table = [
            "  " => PUSH, //
            " \n " => COPY,
            " \t " => COPYN, //
            " \n\t" => EXCHANGE,
            " \n\n" => DROP,
            " \t\n" => SLIDEOFF, //
            "\t   " => ADD,
            "\t  \t" => SUB,
            "\t  \n" => MUL,
            "\t \t " => DIV,
            "\t \t\t" => MOD,
            "\t\t " => STORE,
            "\t\t\t" => READ,
            "\n  " => MARK, //
            "\n \t" => CALL, //
            "\n \n" => JUMP, //
            "\n\t " => JUMPNULL, //
            "\n\t\t" => JUMPDE, //
            "\n\t\n" => ENDFUNC,
            "\n\n\n" => ENDLE,
            "\t\n  " => OUTCHAR,
            "\t\n \t" => OUTNUM,
            "\t\n\t " => INCHAR,
            "\t\n\t\t" => INNUM,
        ];
        $this->token_command = [
            PUSH => "PUSH", //
            COPY => "COPY",
            COPYN => "COPYN", //
            EXCHANGE => "EXCHANGE",
            DROP => "DROP",
            SLIDEOFF => "SLIDEOFF", //
            ADD => "ADD",
            SUB => "SUB",
            MUL => "MUL",
            DIV => "DIV",
            MOD => "MOD",
            STORE => "STORE",
            READ => "READ",
            MARK => "MARK", //
            CALL => "CALL", //
            JUMP => "JUMP", //
            JUMPNULL => "JUMPNULL", //
            JUMPDE => "JUMPDE", //
            ENDFUNC => "ENDFUNC",
            ENDLE => "ENDLE",
            OUTCHAR => "OUTCHAR",
            OUTNUM => "OUTNUM",
            INCHAR => "INCHAR",
            INNUM => "INNUM",
        ];
        $this->bin_op = [PUSH, COPYN, SLIDEOFF, MARK, CALL, JUMP, JUMPNULL, JUMPDE];
        $this->need_number = [PUSH, COPYN, SLIDEOFF];
        $this->need_label = [MARK, CALL, JUMP, JUMPNULL, JUMPDE];
        $this->token_tree = $this->buildStatTree();
    }

    function buildStatTree() {
        $tree = [];
        $tmp = &$tree;
        foreach ($this->token_table as $token => $type) {
            $len = strlen($token);
            for ($i = 0; $i < $len; ++$i) {
                if (isset($tmp[$token[$i]])) {
                    $tmp = &$tmp[$token[$i]];
                } else {
                    $tmp[$token[$i]] = [];
                    $tmp = &$tmp[$token[$i]];
                }
                if ($i == ($len - 1)) {
                    $tmp['t'] = $type;
                }
            }
            $tmp = &$tree;
        }
        return $tree;
    }

    function testNextMatch($str, $index, $len, $tmp) {
        for ($i = $index; $i < $len; ++$i) {
            if (in_array($str[$i], $this->inval)) {
                if (isset($tmp[$str[$i]])) {
                    return array($i, $tmp[$str[$i]]);
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    function getNumber($str, $index, $len) {
        $number = "";
        for ($i = $index; $i < $len; ++$i) {
            if ($str[$i] == "\n") {
                break;
            }
            if (in_array($str[$i], $this->inval)) {
                $number .= $str[$i] == " " ? "0" : "1";
            }
        }
        $symbol = $number[0];
        $number = bindec(substr($number, 1));
        $number = $symbol == "\t" ? -$number : $number;
        return [$i, $number];
    }

    function getLabel($str, $index, $len) {
        $number = "";
        for ($i = $index; $i < $len; ++$i) {
            if ($str[$i] == "\n") {
                break;
            }
            if (in_array($str[$i], $this->inval)) {
                $number .= $str[$i] == " " ? "0" : "1";
            }
        }
        return [$i, $number];
    }

    function getNextToken() {
        $len = strlen($this->str);
        $tmp = $this->token_tree;
        for (; $this->index < $len; ++$this->index) {
            if (in_array($this->str[$this->index], $this->inval)) {
                if (isset($tmp[$this->str[$this->index]])) {
                    $tmp = $tmp[$this->str[$this->index]];
                    if (isset($tmp['t'])) {
                        $ret = $this->testNextMatch($this->str, $this->index + 1, $len, $tmp);
                        if ($ret) {
                            $this->index = $ret[0] - 1;
                            continue;
                        }
                        if (in_array($tmp['t'], $this->bin_op)) {
                            if (in_array($tmp['t'], $this->need_number)) {
                                $number = $this->getNumber($this->str, $this->index + 1, $len);
                            } elseif (in_array($tmp['t'], $this->need_label)) {
                                $number = $this->getLabel($this->str, $this->index + 1, $len);
                            }
                            $this->index = $number[0] + 1;
                            return [$tmp['t'], $number[1]];
                        } else {
                            $this->index = $this->index + 1;
                            return [$tmp['t']];
                        }
                    }
                }
            }
        }
        return NULL;
    }

    function isLabel($token) {
        return $token[0] == MARK;
    }

    function getCmdName($cmd) {
        return $this->token_command[$cmd];
    }

    function hasLabel($token) {
        return in_array($token[0], $this->need_label);
    }

}
