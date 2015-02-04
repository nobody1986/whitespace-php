<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace parser;

class ParserList {

    protected $lexer;

    function __construct($lexer) {
        $this->lexer = $lexer;
    }

    function parse() {
        $ast = [];
        $labels = [];
        $label_offset = [];
        $index = 0;
        $offset = 0;
        while ($token = $this->lexer->getNextToken()) {
            array_push($ast, $token);
            if ($this->lexer->isLabel($token)) {
                $labels[$token[1]] = $index;
                $label_offset[$token[1]] = $offset;
            }
            $offset += sizeof($token);
            ++$index;
        }
        return [$ast, $labels,$label_offset];
    }

}
