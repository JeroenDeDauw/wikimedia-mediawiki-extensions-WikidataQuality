<?php

class QualifierChecker
{

    private $statements;

    public function __construct($statements)
    {
        $this->statements = $statements;
    }

    public function checkQualifierConstraint($propertyId, $dataValueString)
    {
        return new \CheckResult($propertyId, $dataValueString, "Qualifier", '\'\'(none)\'\'', "violation");
    }

    public function checkQualifiersConstraint($propertyId, $dataValueString)
    {
        return new \CheckResult($propertyId, $dataValueString, "Qualifiers", '\'\'(none)\'\'', "todo" );
    }

}