<?php

namespace conversionia\leadflex\helpers;

use conversionia\leadflex\Leadflex;

class SubmissionHelper
{
    // Public Methods
    // =========================================================================
    // Check if a field exists
    public static function cleanPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^\d]/', '', $phone);

        // If longer than 10 digits
        if (strlen($phone) > 10) {
            // Remove leading "1" (if it exists)
            $phone = preg_replace('/^1?/', '', $phone);
        }
        // Return clean phone number
        return $phone;
    }
}
