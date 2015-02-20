<?php

namespace WikidataQuality\ConstraintReport\ConstraintCheck\Checker;

use WikidataQuality\ConstraintReport\ConstraintCheck\Result\CheckResult;

class QualifierChecker
{

    private $statements;
    private $helper;

    public function __construct($statements, $helper)
    {
        $this->statements = $statements;
        $this->helper = $helper;
    }

    public function checkQualifierConstraint($propertyId, $dataValueString)
    {
        return new CheckResult($propertyId, $dataValueString, "Qualifier", '\'\'(none)\'\'', "violation");
    }

    public function checkQualifiersConstraint($propertyId, $dataValueString, $statement, $list)
    {
        $toReplace = array("{", "}", "|", " ");
        $parameterString = $this->helper->limitOutput( 'list: ' . str_replace($toReplace, '', $list) );

        $list = $this->helper->toArray( $list );
        $status = 'compliance';

        foreach( $statement->getQualifiers() as $qualifier ) {
            $pid = $qualifier->getPropertyId()->getSerialization();
            if( !in_array($pid, $list) ){
                $status = 'violation';
                break;
            }
        }
        return new CheckResult($propertyId, $dataValueString, "Qualifiers", $parameterString, $status );
    }

}