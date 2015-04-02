<?php

namespace WikidataQuality\ConstraintReport\Test\CheckResult;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;
use Wikibase\DataModel\Entity\PropertyId;
use DataValues\StringValue;

/**
 * @covers WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult
 *
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class CheckResultTest extends \MediaWikiTestCase {

    private $propertyId;
    private $dataValue;
    private $constraintName;
    private $parameters;
    private $status;
    private $message;

    protected function setUp() {
        parent::setUp();
        $this->propertyId = new PropertyId( 'P1' );
        $this->dataValue = new StringValue( 'Foo' );
        $this->constraintName = 'Range';
        $this->parameters = array();
        $this->status = 'compliance';
        $this->message = 'All right';
    }

    protected function tearDown() {
        parent::tearDown();
        unset( $this->propertyId );
        unset( $this->dataValue );
        unset( $this->constraintName );
        unset( $this->parameters );
        unset( $this->status );
        unset( $this->message );
    }

    public function testConstructAndGetters() {
        $checkResult = new CheckResult( $this->propertyId, $this->dataValue, $this->constraintName, $this->parameters, $this->status, $this->message );
        $this->assertEquals( $this->propertyId, $checkResult->getPropertyId() );
        $this->assertEquals( $this->dataValue, $checkResult->getDataValue() );
        $this->assertEquals( $this->constraintName, $checkResult->getConstraintName() );
        $this->assertEquals( $this->parameters, $checkResult->getParameters() );
        $this->assertEquals( $this->status, $checkResult->getStatus() );
        $this->assertEquals( $this->message, $checkResult->getMessage() );
    }

}