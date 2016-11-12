<?php

/**
 * Copyright (c) 2016-present Ganbaro Digital Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   JsonParser/Internal
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2016-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://code.ganbarodigital.com/php-mv-json-parser
 */

namespace GanbaroDigital\JsonParser\V1\Internal;

use GanbaroDigital\TextParser\V1\Evaluators;
use GanbaroDigital\TextParser\V1\Grammars;
use GanbaroDigital\TextParser\V1\Terminals;

/**
 * what is the JSON grammar?
 */
class JsonGrammar
{
    private static $language = null;

    public static function init()
    {
        self::$language = [
            // grammar
            "object" => new Grammars\GrammarList([
                new Terminals\Lazy\T_OPEN_BRACE,
                new Grammars\Any(new Grammars\Reference("keyValuePairs")),
                new Terminals\Lazy\T_CLOSE_BRACE
            ], new Evaluators\BuildDictionaryFromList('stdClass', 1)),
            "array" => new Grammars\GrammarList([
                new Terminals\Lazy\T_OPEN_SQUARE_BRACKET,
                new Grammars\Any(new Grammars\Reference("values")),
                new Terminals\Lazy\T_CLOSE_SQUARE_BRACKET
            ], new Evaluators\ListEntry(1, [])),
            "keyValuePairs" => new Grammars\AtLeastOnce(
                new Grammars\Reference("keyValuePair"),
                new Grammars\Discard(new Terminals\Lazy\T_COMMA),
                new Evaluators\BuildAssociativeArrayFromList(0, 1)
            ),
            "keyValuePair" => new Grammars\GrammarList([
                new Grammars\Reference("key"),
                new Grammars\Discard(new Terminals\Lazy\T_COLON),
                new Grammars\Reference("value")
            ]),
            "key" => new Grammars\Reference("T_STRING"),
            "value" => new Grammars\AnyOf([
                new Grammars\Reference("T_STRING"),
                new Grammars\Reference("T_NUMBER"),
                new Grammars\Reference("object"),
                new Grammars\Reference("array"),
                new Grammars\Reference("T_TRUE"),
                new Grammars\Reference("T_FALSE"),
                new Grammars\Reference("T_NULL")
            ]),
            "values" => new Grammars\AtLeastOnce(
                new Grammars\Reference("value"),
                new Grammars\Discard(new Terminals\Lazy\T_COMMA)
            ),

            // terminals
            'T_STRING' => new Terminals\Meta\T_DOUBLE_QUOTED_STRING(
                new Evaluators\EvaluatorQueue([
                    new Evaluators\StripQuotes,
                    new Evaluators\UnescapeString,
                    new Evaluators\DecodeUnicode,
                ])
            ),
            'T_NUMBER' => new Terminals\Meta\T_NUMBER,
            'T_TRUE' => new Grammars\PrefixToken("true", new Evaluators\ReturnTrue),
            'T_FALSE' => new Grammars\PrefixToken("false", new Evaluators\ReturnFalse),
            'T_NULL' => new Grammars\PrefixToken("null", new Evaluators\ReturnNull),
        ];
    }

    /**
     * returns the JSON grammar that our parser needs
     *
     * @return array
     */
    public static function getLanguage()
    {
        return self::$language;
    }
}

JsonGrammar::init();
