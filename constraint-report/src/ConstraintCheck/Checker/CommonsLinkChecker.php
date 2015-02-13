<?php

class CommonsLinkChecker {

    public function checkCommonsLinkConstraint( $propertyId, $dataValueString, $statement ) {
        if( $this->isCommonsLinkWellFormed( $dataValueString ) )// TODO: Check format of link
            $status = $this->url_exists( $dataValueString ) ? 'compliance' : 'violation';
        else
            $status = 'violation';
        $this->addOutputRow($propertyId, $dataValueString, 'Commons  link', '\'\'(none)\'\'', $status);
    }

    private function url_exists($url) {
        if (!$fp = curl_init($url)) return false;
        return true;
    }

    private function isCommonsLinkWellFormed( $dataValueString ) {
        $toReplace = array("_", ":", "%20", "http://");
        $compareString = trim( str_replace( $toReplace, '', $dataValueString) );
        return $dataValueString == $compareString;
    }

}