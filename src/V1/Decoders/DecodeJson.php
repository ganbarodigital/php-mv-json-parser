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
 * @package   JsonParser/Decoders
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2016-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://code.ganbarodigital.com/php-mv-json-parser
 */

namespace GanbaroDigital\JsonParser\V1\Decoders;

use InvalidArgumentException;
use GanbaroDigital\JsonParser\V1\Exceptions\CannotDecodeJson;
use GanbaroDigital\JsonParser\V1\Internal\JsonGrammar;
use GanbaroDigital\TextParser\V1\Lexer\ApplyGrammar;
use GanbaroDigital\TextParser\V1\Lexer\WhitespaceAdjuster;

/**
 * decode a JSON-encoded string
 */
class DecodeJson
{
    /**
     * decode a JSON-encoded string
     *
     * @param  string $rawJson
     *         the value we're going to decode
     * @return array|object
     *         the decoded data
     * @throws InvalidArgumentException
     *         if $rawJson isn't a supported data type
     * @throws CannotDecodeJson
     *         if something goes wrong
     */
    public static function from($rawJson)
    {
        // robustness!
        if (!is_string($rawJson)) {
            throw new InvalidArgumentException('$rawJson is not a string');
        }

        // get the grammar for JSON
        $language = JsonGrammar::getLanguage();

        // is the string valid JSON?
        $matches = ApplyGrammar::to($language, 'value', $rawJson, 'json', new WhitespaceAdjuster);
        if (!$matches['matched']) {
            // apparently not :(
            throw CannotDecodeJson::newFromInputParameter(
                $rawJson, '$rawJson', [
                    'parser_error' => "Expected " . $matches['expected']->getPseudoBNF() . " at line "
                         . $matches['position']->getLineNumber() . ', column '
                         . $matches['position']->getLineOffset()
                ]
            );
        }

        // convert the parsed tokens into a value
        return $matches['value']->evaluate();

        // at this point, the JSON _should_ decode successfully
        //
        // however ... just in case ...
        $retval = json_decode($rawJson);
        if (!is_object($retval) && !is_array($retval)) {
            throw CannotDecodeJson::newFromInputParameter(
                $rawJson, '$rawJson', [
                    'PHP_error' => json_last_error_msg()
                ]
            );
        }

        // if we get here, then all is well
        return $retval;
    }
}
