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
    
    function parse(){
        $ast = [];
        $labels = [];
        $index = 0;
//        $tmp = &$ast;
        /**
         *
         *  while ($token = $this->lexer->getNextToken()) {
            $tmp['left'] = isset($token[1])?$token[1]:[];
            $tmp['right'] = [];
            $tmp['t'] = $token[0];
            $tmp = &$tmp['right'];
        }
         */
        while($token = $this->lexer->getNextToken()){
//            $tmp['left'] = $token;
//            $tmp['right'] = [];
//            $tmp = &$tmp['right'];
            array_push($ast, $token);
            if($this->lexer->isLabel($token)){
                $labels[$token[1]] = $index;
            }
            ++$index;
        }
        return [$ast,$labels];
    }

}
