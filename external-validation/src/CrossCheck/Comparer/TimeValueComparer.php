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

        // Parse external datetime
        $externalDateTime = DateTime::createFromFormat( $this->dumpMetaInformation->getDateFormat(), $this->externalValues[ 0 ] );

        // Format output values
        $this->formatValues( $localDateTime, $externalDateTime );

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

            case TimeValue::PRECISION_10a:
                $result = $result && $diff->y < 10;

            case TimeValue::PRECISION_100a:
                $result = $result && $diff->y < 100;

            case TimeValue::PRECISION_ka:
                $result = $result && $diff->y < 1000;

            case TimeValue::PRECISION_10ka:
                $result = $result && $diff->y < 10000;

            case TimeValue::PRECISION_100ka:
                $result = $result && $diff->y < 100000;

            case TimeValue::PRECISION_Ma:
                $result = $result && $diff->y < 1000000;

            case TimeValue::PRECISION_10Ma:
                $result = $result && $diff->y < 10000000;

            case TimeValue::PRECISION_100Ma:
                $result = $result && $diff->y < 100000000;

            case TimeValue::PRECISION_Ga:
                $result = $result && $diff->y < 1000000000;
                break;

            default:
                $result = false;
        }

        return $result;
    }


    /**
     * Sets local and external values to formatted dates depending on precision.
     * @param DateTime $local
     * @param DateTime $external
     */
    private function formatValues( $local, $external ) {
        // Determine date format
        switch ( $this->dataValue->getPrecision() ) {
            case TimeValue::PRECISION_SECOND:
                $format = "Y-m-d H:i:s";
                break;

            case TimeValue::PRECISION_MINUTE:
                $format = "Y-m-d H:i";
                break;

            case TimeValue::PRECISION_HOUR:
                $format = "Y-m-d H:0";
                break;

            case TimeValue::PRECISION_DAY:
                $format = "Y-m-d";
                break;

            case TimeValue::PRECISION_MONTH:
                $format = "Y-m";
                break;

            case TimeValue::PRECISION_YEAR:
                $format = "Y";
                break;

            case TimeValue::PRECISION_10a:
                $format = "Y ±10";
                break;

            case TimeValue::PRECISION_100a:
                $format = "Y ±100";
                break;

            case TimeValue::PRECISION_ka:
                $format = "Y ±1000";
                break;

            case TimeValue::PRECISION_10ka:
                $format = "Y ±1000";
                break;

            case TimeValue::PRECISION_100ka:
                $format = "Y ±10000";
                break;

            case TimeValue::PRECISION_Ma:
                $format = "Y ±100000";
                break;

            case TimeValue::PRECISION_10Ma:
                $format = "Y ±1000000";
                break;

            case TimeValue::PRECISION_100Ma:
                $format = "Y ±10000000";
                break;

            case TimeValue::PRECISION_Ga:
                $format = "Y ±100000000";
                break;

            default:
                $format = "Y-m-d H:i:s";
                break;
        }

        // Set properties to formatted dates
        $this->localValues = array( $local->format( $format ) );
        $this->externalValues = array( $external->format( $format ) );
    }
}