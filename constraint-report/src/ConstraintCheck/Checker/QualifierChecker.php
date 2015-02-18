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

    public function checkQualifiersConstraint($propertyId, $dataValueString)
    {
        return new CheckResult($propertyId, $dataValueString, "Qualifiers", '\'\'(none)\'\'', "todo" );
    }

}