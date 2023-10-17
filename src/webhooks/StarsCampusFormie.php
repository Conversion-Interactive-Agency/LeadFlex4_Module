<?php
namespace conversionia\leadflex\webhooks;

use Craft;
use craft\base\Volume;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\Formie;
use verbb\formie\integrations\webhooks\Webhook;

// Volume Types
use craft\base\LocalVolumeInterface;

class StarsCampusFormie extends Webhook
{
    public $webhook;

    public static function displayName(): string
    {
        return Craft::t('formie', 'Stars Campus ATS');
    }

    public function getDescription(): string
    {
        return Craft::t('formie', 'This is a Stars Campus webhook integration.');
    }

    public function getIconUrl(): string
    {
        return Craft::$app->getAssetManager()->getPublishedUrl("@verbb/formie/web/assets/webhooks/dist/img/webhook.svg", true);
    }

    public function getSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate("formie/integrations/webhooks/webhook/_plugin-settings", [
            'integration' => $this,
        ]);
    }

    public function getFormSettingsHtml($form): string
    {
        return Craft::$app->getView()->renderTemplate("formie/integrations/webhooks/webhook/_form-settings", [
            'integration' => $this,
            'form' => $form,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function generatePayloadValues(Submission $submission): array
    {
        /** @var Form $form */
        $form = $submission->getForm();
        $fields = $form->getFields();
        $fileUploadFields = [];

        // Initialize form data
        $data = [];
        $labels = [];

        $uploads = [];

        // Get every submitted field value
        foreach ($fields as $field) {

            // Get data
            $value = $submission->getFieldValue($field->handle);
            $data[$field->handle] = $field->getValueAsString($value, $submission);

            // Get labels
            $label = $field->getAttributeLabel($field->handle);
            $labels[$field->handle] = $label;

            // Fields with default values
            $useDefaults = [
                'atsCompanyId',
                'companyName',
            ];

            //Collect File Upload Field Types
            if ($field->getType() === 'verbb\formie\fields\formfields\FileUpload') {
                $fileUploadFields[] = $field->handle;
                $upload['handle'] = $field->handle;
                $upload['name'] = $field->name;
                $assets = $submission->getFieldValue($field->handle)->all();
                foreach ($assets as $asset)
                {
                    $postAsset['filename'] = $asset->filename;
                    $postAsset['title'] = $asset->title;
                    $postAsset['kind'] = $asset->kind;
                    $postAsset['data'] = $asset->getDataUrl();
                    $uploads[] = $postAsset;
                }
            }

            // Fallback to default value if necessary
            if (in_array($field->handle, $useDefaults) && !$data[$field->handle]) {
                $data[$field->handle] = $field->defaultValue;
            }
        }

        // Get phone number
        $data['cellPhone'] = ($data['cellPhone'] ?? '');

        // Get driver ID
        $driverId = $data['webPageUrl'].'?driverId='.$submission->id;

        // Get license class
        // $licenseClass = ('Yes' === $data['cdlA'] ? 'CDL-A' : '');

        // Data has already been assigned
        $requiredFields = [
            'companyName','atsCompanyId','referrerValue',
            'firstName','lastName',
            'city','state','zipCode',
            'email','cellPhone','optIn',
        ];

        // Compile JSON data
        $json = [
            'form' => [
                'id' => $form->id,
                'name' => $form->title,
                'returnUrl' => $form->getRedirectUrl(),
                'CompanyName' => trim($data['companyName']),
                'CompanyId' => trim($data['atsCompanyId']),
                'DriverId' => $driverId,
                'AppReferrer' => trim($data['referrerValue']),
                'GivenName' => trim($data['firstName']),
                'FamilyName' => trim($data['lastName']),
                'Municipality' => trim($data['city']),
                'Region' => trim($data['state']),
                'PostalCode' => trim($data['zipCode']),
                'InternetEmailAddress' => trim($data['email']),
                'PrimaryPhone' => $this->_cleanPhone($data['cellPhone']),
                // 'CommercialDriversLicense' => $data['cdlA'],
                // 'LicenseClass' => $licenseClass,
                'OptIn' => ($data['optIn'] ?? null || 'No' ?: 'No' ),
            ],
        ];

        if (!empty($uploads)){
            $json['uploads'] = $uploads;
        }

        $usedFields = array_merge($requiredFields, $fileUploadFields);

        // Loop through form data
        foreach ($data as $handle => $value) {
            // If data point was not used, add to JSON data
            if (!in_array($handle, $usedFields)) {
                $label = ($labels[$handle] ?? $handle);
                $json[$label] = $value;
            }
        }

        // Return JSON data
        return [
            'json' => $json
        ];
    }

    /**
     * Strip all formatting from phone number.
     *
     * @param string $phone
     * @return string
     */
    private function _cleanPhone(string $phone): string
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
