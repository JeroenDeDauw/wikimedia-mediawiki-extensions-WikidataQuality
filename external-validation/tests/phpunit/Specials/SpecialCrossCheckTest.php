<?php

namespace WikidataQuality\ExternalValidation\Tests\Specials\SpecialCrossCheck;

use Language;
use SpecialPage;
use Title;
use Wikibase\Test\SpecialPageTestBase;
use WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck;
use WikidataQuality\Html\HtmlTable;

/**
 * @covers WikidataQuality\ExternalValidation\Specials\SpecialCrossCheck
 *
 * @author BP2014N1
 * @license GNU GPL v2+exte
 */
class SpecialCrossCheckTest extends SpecialPageTestBase {

    protected function newSpecialPage() {
        $page = new SpecialCrossCheck();

        $languageNameLookup = $this->getMock( 'Wikibase\Lib\LanguageNameLookup' );
        $languageNameLookup->expects( $this->any() )
            ->method( 'getName' )
            ->will( $this->returnValue( 'LANGUAGE NAME' ) );

        return $page;
    }


    public function requestProvider()
    {
        $cases = array();
        $matchers = array();

        // Empty input
        $matchers['entityId'] = array(
            'tag' => 'input',
            'attributes' => array(
                'id' => 'wdq-crosscheck-entityid',
                'placeholder' => 'Qxx',
                'name' => 'entityId',
                'class' => 'mw-ui-input'
            )
        );

        $matchers['submit'] = array(
            'tag' => 'input',
            'attributes' => array(
                'id' => 'wdq-crosscheck-submit',
                'type' => 'submit',
                'value' => 'Cross-Check',
                'name' => 'submit'
            )
        );

        $cases['empty'] = array('', array(), null, $matchers);

        // Invalid input (en)
        $matchers['error'] = array(
            'tag' => 'p',
            'attributes' => array(
                'class' => 'wdq-crosscheck-error'
            ),
            'content' => 'The given input string is not a string that could be parsed to an entityId.'
        );

        $cases['invalid input 1'] = array( 'Qwertz', array(), 'en', $matchers );
        $cases['invalid input 2'] = array( '300', array(), 'en', $matchers );

        unset( $matchers['error'] );

        $matchers['result for'] = array(
            'tag' => 'h3',
            'content' => 'Result for'
        );

        $matchers['error'] = array(
            'tag' => 'p',
            'attributes' => array(
                'class' => 'wdq-crosscheck-error'
            ),
            'content' => 'Item does not exist!'
        );

        $cases['valid input - not existing item'] = array( 'Q99999999', array(), 'en', $matchers );

        return $cases;
    }

    /**
     * @dataProvider requestProvider
     *
     * @param string $sub The subpage parameter to call the page with
     * @param WebRequest|null $request Web request that may contain URL parameters, etc
     * @param string $userLanguage The language code which should be used in the context of this special page
     * @param $matchers
     */
    public function testExecute( $sub, $request, $userLanguage, $matchers ) {
        $request = new \FauxRequest( $request );

        list( $output, ) = $this->executeSpecialPage( $sub, $request, $userLanguage );
        foreach( $matchers as $key => $matcher ) {
            $this->assertTag( $matcher, $output, "Failed to match html output with tag '{$key}'" );
        }
    }

}
 