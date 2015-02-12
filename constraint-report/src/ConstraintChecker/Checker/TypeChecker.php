<?php

class TypeChecker {

    private $statements;

    public function __construct($statements)
    {
        $this->statements = $statements;
    }

    // TODO
    public function checkTypeConstraint( $propertyId, $dataValueString ) {
        return new \CheckResult($propertyId, $dataValueString, "Type", '\'\'(none)\'\'', "todo" );
    }

    // TODO
    public function checkValueTypeConstraint( $propertyId, $dataValueString ) {
        return new \CheckResult($propertyId, $dataValueString, "Value type", '\'\'(none)\'\'', "todo" );
    }
}