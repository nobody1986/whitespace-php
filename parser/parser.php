<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace parser;

class Parser {

    protected $lexer;

    function __construct($lexer) {
        $this->lexer = $lexer;
    }
    
    function parse(){
        $ast = [];
        $tmp = &$ast;
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
//            var_dump($token);
            $tmp['left'] = $token;
            $tmp['right'] = [];
            $tmp = &$tmp['right'];
        }
        return $ast;
    }

}
