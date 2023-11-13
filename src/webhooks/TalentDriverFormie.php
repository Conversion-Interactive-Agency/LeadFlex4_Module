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

// todo: Build postFowarinding from Digital Ocean;
// use vaersaagod\dospaces\Volume as DigitalOceanVolume;

class TalentDriverFormie extends Webhook
{
    public $webhook;

    public static function displayName(): string
    {
        return Craft::t('formie', 'Talent Driver');
    }

    public function getDescription(): string
    {
        return Craft::t('formie', 'Talent Driver webhook integration.');
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

        // Initialize form data
        $data = [];
        $labels = [];

        $requiredFields = [];
        // Data has already been assigned

        $requiredFieldsMapping = [
            'firstName' => 'given_name',
            'lastName' => 'family_name',
            'cellPhone' => 'phone',
            'email' => 'email',
            'birthDate' => 'birth_date',
            'city' => 'city',
            'state' => 'state',
            'zipCode' => 'zip',
            'optIn' => 'sms_consent',
            'experience' => 'experience',
            'accidents' => 'accidents',
            'violations' => 'violations',
            'referrerValue' => 'source',
        ];

        // Get every submitted field value
        foreach ($fields as $field) {
            // Get data
            $value = $submission->getFieldValue($field->handle);
            $data[$field->handle] = trim($field->getValueAsString($value, $submission));
            unset($requiredFieldsMapping[$field->handle]);
            // Get labels
            $label = $field->getAttributeLabel($field->handle);
            $labels[$field->handle] = $label;

            // Fields with default values
            $useDefaults = [
                'atsCompanyId',
                'companyName',
            ];

            // Fallback to default value if necessary
            if (in_array($field->handle, $useDefaults) && !$data[$field->handle]) {
                $data[$field->handle] = $field->defaultValue;
            }
        }

        // Get phone number
        $data['cellPhone'] = ($data['cellPhone'] ?? '');

        // Get driver ID
        $driverId = $data['webPageUrl'].'?driverId='.$submission->id;

        // Check conditions if should be sent to testing environment
        $setAsTestSubmission =
            $data['firstName'] == $data['lastName'] ||
            $data['firstName'] == 'test' ||
            $data['lastName'] == 'test';
        $company = !$setAsTestSubmission ? $data['companyName'] : 'test';

        // Compile JSON data
        $json = [
            'params' => [
                'company' => $company
            ],
            'headers'=>[],
            'body' => [
                "given_name"=>  trim($data['firstName']),
                "family_name"=> trim($data['lastName']),
                "phone"=> $this->_cleanPhone($data['cellPhone']),
                "email"=> trim($data['email']),
                "birth_date"=> null,
                "state"=> strtoupper(trim($data['state'])),
                "city"=> trim($data['city']),
                "zip"=> trim($data['zipCode']),
                "sms_consent"=>  (boolean)($data['optIn'] ?? null || False ?: False),
                "experience"=> $data['experience'],
                "accidents"=> $data['accidents'],
                "violations"=> $data['violations'],
                "source"=> 5, // 5 - JobsInTrucks
            ],
            'id' => $form->id,
            'name' => $form->title,
            'submission' => $submission->id,
            'returnUrl' => $form->getRedirectUrl(),
            'AppReferrer' => trim($data['referrerValue']),
            'CompanyName' => trim($data['companyName']),
            'CompanyId' => trim($data['atsCompanyId']),
            'DriverId' => $driverId,
        ];

        if (!empty($uploads)){
            $json['uploads'] = $uploads;
        }

        $usedFields = $requiredFields;

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
