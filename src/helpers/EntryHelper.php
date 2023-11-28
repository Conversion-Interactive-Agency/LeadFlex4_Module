<?php

namespace conversionia\leadflex\helpers;

use conversionia\leadflex\Leadflex;

class EntryHelper
{
    // Public Methods
    // =========================================================================
    // Check if a field exists
    public static function doFieldsExists($entry, $fieldHandle): bool
    {
        if (!self::isJobEntry($entry)) {
            return false;
        }

        $hasAllFields = true;

        if (!is_array($fieldHandle)){
            $fieldHandle = [$fieldHandle];
        }

        $entryFields = $entry->getType()->getFieldLayout()->getFields();

        // transform the array of Field objects into an array of field handles for convenience
        $entryFieldHandles = array_column($entryFields, 'handle');

        // check entry has fields
        foreach ($fieldHandle as $handle) {
            $entryHasMyCustomField = in_array($handle, $entryFieldHandles);
            if (!$entryHasMyCustomField) {
                $hasAllFields = false;
            }
        }

        return $hasAllFields;
    }

    public static function isJobEntry($entry):bool
    {
        if(!$entry instanceof Entry){
            return false;
        }
        return Leadflex::class->section == $entry->section->handle;
    }
}
