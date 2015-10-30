<?php

namespace ImpressCMS\Tests\Libraries\ICMS;

class DataFilterTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * Test if icms_core_DataFilter is available
     */
    public function testAvailability() {
        $this->assertTrue(class_exists('icms_core_DataFilter', true), "icms_core_DataFilter class doesn't exist");        
    }
    
    /**
     * Tests if static variables has correct values
     */
    public function testStaticVariables() {
        $this->assertInternalType('array', \icms_core_DataFilter::$allSmileys, '$allSmileys must be an array');
        $this->assertInternalType('array', \icms_core_DataFilter::$displaySmileys, '$displaySmileys must be an array');
    }
    
    /**
     * Checks if all required static methdos are available
     */
    public function testMethodsAvailability() {
         foreach ([ 'filterDebugInfo', '_filterImgUrl', 'checkUrlString', 'nl2Br', 'htmlSpecialChars', 'undoHtmlSpecialChars', 'htmlEntities', 'addSlashes', 'stripSlashesGPC', 'cleanArray', 'checkVar', 'checkVarArray', 'filterTextareaInput', 'filterTextareaDisplay', 'filterHTMLinput', 'filterHTMLdisplay', 'codeDecode', 'makeClickable', 'smiley', 'getSmileys', 'censorString', 'codePreConv', 'codeConv', 'codeSanitizer', 'codeDecode_extended', 'loadExtension', 'executeExtension', 'textsanitizer_syntaxhighlight', 'textsanitizer_php_highlight', 'textsanitizer_geshi_highlight', 'icms_trim', 'utf8_strrev', 'icms_substr', 'priv_checkVar', 'priv_smiley', 'priv_getSmileys' ] as $method) {
             $this->assertTrue(method_exists('icms_core_DataFilter', $method), $method . ' doesm\'t exists');
         }
    }
    
}