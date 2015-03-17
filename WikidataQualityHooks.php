<?php

final class WikidataQualityHooks
{
    /**
     * @param DatabaseUpdater $updater
     * @return bool
     */
    public static function onCreateSchema( DatabaseUpdater $updater )
    {
        $updater->addExtensionTable( 'wdq_dump_information', __DIR__ . '/external-validation/sql/create_wdq_dump_information.sql', true );
        $updater->addExtensionTable( 'wdq_external_data', __DIR__ . '/external-validation/sql/create_wdq_external_data.sql', true );
        $updater->addExtensionTable( 'constraints_ready_for_migration', __DIR__ . '/constraint-report/sql/create_constraints_ready_for_migration.sql', true );

        return true;
    }

    public static function onUnitTestsList( &$files )
    {
        $files = array_merge( $files, glob( __DIR__ . '/external-validation/tests/phpunit/*Test.php' ), glob( __DIR__ . '/constraint-report/tests/phpunit/*Test.php' ) );
        return true;
    }
}