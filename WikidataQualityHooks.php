<?php

final class WikidataQualityHooks
{
    /**
     * @param DatabaseUpdater $updater
     * @return bool
     */
    public static function onCreateSchema( DatabaseUpdater $updater )
    {
        $updater->addExtensionTable( 'wdq_constraints', __DIR__ . '/constraint-report/sql/create_wdq_constraints.sql' );
        $updater->addExtensionTable( 'wdq_dump_information', __DIR__ . '/external-validation/sql/create_wdq_dump_information.sql' );
        $updater->addExtensionTable( 'wdq_external_data', __DIR__ . '/external-validation/sql/create_wdq_external_data.sql' );

        return true;
    }

    public static function onUnitTestsList( &$files )
    {
        $files = array_merge( $files, glob( __DIR__ . '/external-validation/tests/phpunit/*Test.php' ), glob( __DIR__ . '/constraint-report/tests/phpunit/*Test.php' ) );
        return true;
    }
}