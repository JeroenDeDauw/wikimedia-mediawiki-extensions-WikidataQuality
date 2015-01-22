<?php

final class WikidataQualityHooks
{
    /**
     * @param DatabaseUpdater $updater
     * @return bool
     */
    public static function onCreateSchema( DatabaseUpdater $updater )
    {
        $updater->addExtensionTable( 'wbq_external_data', __DIR__ . '/external-validation/sql/create_wbq_external_data.sql', true );
        $updater->addExtensionTable( 'wbq_constraints_from_templates', __DIR__ . '/constraint-report/sql/create_wbq_constraints_from_templates.sql', true );

        return true;
    }
}