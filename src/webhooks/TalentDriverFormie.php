<?php
namespace conversionia\leadflex\webhooks;

use Craft;
use craft\helpers\App;
use craft\base\Volume;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;
use verbb\formie\Formie;
use verbb\formie\integrations\webhooks\Webhook;

use conversionia\leadflex\helpers\SubmissionHelper;
use conversionia\leadflex\events\ReturnJsonEvent;

class TalentDriverFormie extends Webhook
{
    public $webhook;

    const EVENT_BEFORE_RETURN_JSON = 'beforeReturnJson';

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
        $json = [
            'params' => [],
            'headers' => [],
            'body' => [],
            'form' => [],
            'company' => [],
        ];
        
        $data = [];

        $requiredFieldsMapping = [
            'firstName' => 'body.given_name',
            'lastName' => 'body.family_name',
            'cellPhone' => 'body.phone',
            'email' => 'body.email',
            'dateOfBirth' => 'body.birth_date',
            'city' => 'body.city',
            'state' => 'body.state',
            'zipCode' => 'body.zip',
            'optIn' => 'body.sms_consent',
            'experience' => 'body.experience',
            'accidents' => 'body.accidents',
            'movingViolations' => 'body.violations',
            'referrerValue' => 'body.source',
        ];

        $useDefaults = [
            'atsCompanyId',
            'companyName',
        ];

        // Get every submitted field value
        foreach ($fields as $field) {
            // Get data
            $value = $submission->getFieldValue($field->handle);
            $data[$field->handle] = trim($field->getValueAsString($value, $submission));
            unset($requiredFieldsMapping[$field->handle]);

            // Fallback to default value if necessary
            if (in_array($field->handle, $useDefaults) && !$data[$field->handle]) {
                $data[$field->handle] = $field->defaultValue;
            }
        }

        if (!empty($requiredFieldsMapping)) {
            $message = 'Missing required fields: ' . implode(', ', array_keys($requiredFieldsMapping));
            Craft::error($message, 'application');
            return [];
        }

        // Test and fallbacks for development
        // Check conditions if should be sent to testing environment
        $setAsTestSubmission =
            $data['firstName'] == $data['lastName'] ||
            $data['firstName'] == 'test' ||
            $data['lastName'] == 'test';
        $company = !$setAsTestSubmission ? $data['companyName'] : 'test';
        $GcpBucket = explode('.',App::env('PRODUCTION_DOMAIN'))[0] ?? 'dev';

        // Compile JSON data
        $json = [
            'params' => [
                'company' => $company
            ],
            'headers'=>[],
            'body' => [
                "given_name"=>  trim($data['firstName']),
                "family_name"=> trim($data['lastName']),
                "phone"=> SubmissionHelper::cleanPhone($data['cellPhone']),
                "email"=> trim($data['email']),
                "birth_date"=> $data['dateOfBirth'],
                "state"=> strtoupper(trim($data['state'])),
                "city"=> trim($data['city']),
                "zip"=> trim($data['zipCode']),
                "sms_consent"=>  (boolean)($data['optIn'] ?? null || False ?: False),
                "experience"=> (int)$data['experience'],
                "accidents"=> (int)$data['accidents'],
                "violations"=> (int)$data['movingViolations'],
                "source"=> 5, // 5 - JobsInTrucks
            ],
            'form'=>[
                'id' => $form->id,
                'name' => $form->title,
                'returnUrl' => $form->getRedirectUrl(),
            ],
            'submission' => [
                'id' => $submission->id,
                'webPageUrl' => $data['webPageUrl'],
                'referrer' => trim($data['referrerValue']),
            ],
            'company' => [
                'id' => trim($data['atsCompanyId']),
                'name' => trim($data['companyName']),
                'domain' => App::env('PRODUCTION_DOMAIN'),
            ],
            'gcp' => [
                'bucket' => $GcpBucket,
            ],
        ];

        /*
        ToDo: Add event to modify JSON data before sending
        $event = new ReturnJsonEvent([
            'data' => $data,
            'form' => $form,
            'json' => $json,
            'submission' => $submission,
            'webhook' => $this,
        ]);

         if ($this->hasEventHandlers(self::EVENT_BEFORE_RETURN_JSON)) {
            $this->trigger(self::EVENT_BEFORE_RETURN_JSON, $event);
            $json = $event->json;
         }
        */

        // Return JSON data
        return [
            'json' => $json
        ];
    }
}
