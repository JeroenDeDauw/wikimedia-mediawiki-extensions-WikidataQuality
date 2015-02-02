<?php

namespace WikidataQuality\ExternalValidation\CrossCheck\Comparer;


use DataValues\TimeValue;
use DateTime;


/**
 * Class TimeValueComparer
 * @package WikidataQuality\ExternalValidation\CrossCheck\Comparer
 * @author BP2014N1
 * @license GNU GPL v2+
 */
class TimeValueComparer extends DataValueComparer
{
    /**
     * Array of DataValue classes that are supported by the current comparer.
     * @var array
     */
    public static $acceptedDataValues = array( 'DataValues\TimeValue' );


    /**
     * Starts the comparison of given TimeValue and values of external database.
     * @return bool - result of the comparison.
     */
    public function execute()
    {
        // Parse local datetime
        $value = substr( $this->dataValue->getTime(), 8 );
        $localDateTime = DateTime::createFromFormat( 'Y-m-d\TH:i:s\Z', $value );
        $this->localValues = array( $localDateTime->format('Y-m-d\TH:i:s\Z') );

        // Parse external datetime
        $externalDateTime = DateTime::createFromFormat( 'd.m.Y', $this->externalValues[ 0 ] );
        $this->externalValues = array( $externalDateTime->format('Y-m-d\TH:i:s\Z') );

        // Compare value
        $result = true;
        $diff = date_diff( $localDateTime, $externalDateTime );
        switch ( $this->dataValue->getPrecision() ) {
            case TimeValue::PRECISION_SECOND:
                $result = $diff->s == 0;

            case TimeValue::PRECISION_MINUTE:
                $result = $result && $diff->i == 0;

            case TimeValue::PRECISION_HOUR:
                $result = $result && $diff->h == 0;

            case TimeValue::PRECISION_DAY:
                $result = $result && $diff->d == 0;

            case TimeValue::PRECISION_MONTH:
                $result = $result && $diff->m == 0;

            case TimeValue::PRECISION_YEAR:
                $result = $result && $diff->y == 0;
                break;

            default:
                $result = false;
        }

        return $result;
    }
}