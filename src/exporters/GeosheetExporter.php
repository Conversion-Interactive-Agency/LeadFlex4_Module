<?php

namespace conversionia\leadflex\exporters;

use craft\base\Element;
use craft\elements\Entry;
use craft\base\ElementExporter;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;

class GeosheetExporter extends ElementExporter
{
    public static function displayName(): string
    {
        return 'GeoSheet';
    }

    public function export(ElementQueryInterface $query): array
    {
        $results = [];

        // Eager-load the entries related via the relatedEntries field
        /** @var ElementQuery $query */
        $query->with(['defaultJobDescription', 'assignedCampaign']);

        foreach ($query->each() as $element) {

            // Eager loaded defaultJobDescription
            $fallback = $element->defaultJobDescription[0] ?? '';

            $lastUpdatedBy = $element->currentRevision ->creator->email ?? $element->author->email ?? 'No Author Assigned (Bulk Edit or FeedMe)' ;

            $campaign = !empty($element->assignedCampaign[0])
                ? $element->assignedCampaign[0]->title
                : '';

            /** @var Element $element */
            $results[] = [
                'ID' => $element->id,
                'Title' => $element->adHeadline ?? $fallback->adHeadline ?? '',
                'Status' => ucfirst($element->status),
                'URL' => $element->getUrl(),
                'Trailer Type' => $element->trailerType->label ?? '',
                'Driver Type' => $element->driverType->label ?? '',
                'Route Type' => $element->jobType->label ?? '',
                'Job Type' => $element->jobType->label ?? '',
                'Assigned Campaign' => $campaign,
                'Hiring Radius' => $element->hiringRadius ?? $fallback->hiringRadius ?? '',
                'Google Jobs Title' => $element->googleJobsTitle ?? '',
                'Ad Headline' => $element->adHeadline ?? $fallback->adHeadline ?? '',
                'Location' => $element->location->formatted ?? '',
                'City' => $element->location->city ?? '',
                'State' => $element->location->state ?? '',
                'Zip' => $element->location->zip ?? '',
                'Custom UTM Tag' => $element->customUtmTag ?? '',
                'External Job ID' => $element->externalJobId ?? '',
                'Pay' => $element->pay ?? $fallback->pay ?? '',
                'Last Updated' => $element->dateUpdated->format('Y-m-d H:i:s'),
                'Last Updated By' => $lastUpdatedBy ?? '',
            ];
        }

        return $results;
    }
}
