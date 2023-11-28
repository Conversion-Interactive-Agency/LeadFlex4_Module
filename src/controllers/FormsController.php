<?php
namespace conversionia\leadflex\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use verbb\formie\elements\Form;
use yii\web\Response;

class FormsController extends Controller
{

    protected $allowAnonymous = true;

    public function actionJson(): Response
    {
        // Get slug of requested form
        $slug = Craft::$app->getRequest()->getSegment(2);

        // If no slug provided, bail with error message
        if (!$slug) {
            return $this->asJson(['error' => "Missing form handle."]);
        }

        // Get specified form
        $form = Form::find()->handle($slug)->one();

        // If unable to find the form, bail with error message
        if (!$slug) {
            return $this->asJson(['error' => "Formie form {$slug} cannot be found."]);
        }

        // Get form fields
        $fields = ($form->getFormFieldLayout()->getFields() ?? []);

        // If unable to retrieve fields, bail with error message
        if (!$fields) {
            return $this->asJson(['error' => "Unable to retrieve form fields."]);
        }

        // Initialize JSON packet
        $json = [];

        // Loop through form fields
        foreach ($fields as $field) {

            switch (get_class($field)) {
                case 'verbb\formie\fields\formfields\Hidden':
                    $json[] = $this->_hidden($field);
                    break;
                case 'verbb\formie\fields\formfields\SingleLineText':
                    $json[] = $this->_singleLineText($field);
                    break;
                case 'verbb\formie\fields\formfields\Phone':
                    $json[] = $this->_phone($field);
                    break;
                case 'verbb\formie\fields\formfields\Email':
                    $json[] = $this->_email($field);
                    break;
                case 'verbb\formie\fields\formfields\Dropdown':
                case 'verbb\formie\fields\formfields\Radio':
                    $json[] = $this->_select($field);
                    break;
                case 'verbb\formie\fields\formfields\Agree':
                    $json[] = $this->_agree($field);
                    break;
                case 'verbb\formie\fields\formfields\FileUpload':
                    $json[] = $this->_fileUpload($field);
                    break;
                default:
                    continue 2;
            }

        }

        // Return complete field data
        return $this->asJson($json);
    }

    // ================================================================================ //

    /**
     * Get the JSON representation of a Formie hidden field.
     *
     * @param $field
     * @return array
     */
    private function _common($field): array
    {
        return [
            'id' => $field->handle,
            'type' => 'text',
            'question' => $field->name,
            'required' => (bool) $field->required,
        ];
    }

    // ================================================================================ //

    /**
     * Get the JSON representation of a Formie hidden field.
     *
     * @param $field
     * @return array
     */
    private function _hidden($field): array
    {
        return array_merge($this->_common($field), [
//            'type' => 'text',
        ]);
    }

    /**
     * Get the JSON representation of a Formie singleLineText field.
     *
     * @param $field
     * @return array
     */
    private function _singleLineText($field): array
    {
        return array_merge($this->_common($field), [
//            'type' => 'text',
        ]);
    }

    /**
     * Get the JSON representation of a Formie phone field.
     *
     * @param $field
     * @return array
     */
    private function _phone($field): array
    {
        return array_merge($this->_common($field), [
//            'type' => 'text',
        ]);
    }

    /**
     * Get the JSON representation of a Formie email field.
     *
     * @param $field
     * @return array
     */
    private function _email($field): array
    {
        return array_merge($this->_common($field), [
//            'type' => 'text',
        ]);
    }

    // ================================================================================ //

    /**
     * Get the JSON representation of a Formie dropdown field.
     *
     * @param $field
     * @return array
     */
    private function _select($field): array
    {
        // Remove isDefault attribute from each option
        array_walk($field->options, function (&$opt) {
            unset($opt['isDefault']);
        });

        // If fewer than 5 options
        if (count($field->options) < 5) {
            // Walk through options array
            array_walk($field->options, function (&$opt, $key) use ($field) {
                // If option is blank
                if (!($opt['value'] || $opt['value'] === '0')) {
                    // Remove it
                    unset($field->options[$key]);
                }
            });
        }

        return array_merge($this->_common($field), [
            'type' => 'select',
            'options' => array_values($field->options),
        ]);
    }

    // ================================================================================ //

    /**
     * Get the JSON representation of a Formie agree field.
     *
     * @param $field
     * @return array
     */
    private function _agree($field): array
    {
        // Get label from description
        $description = Json::decode($field->description);
        $label = ($description[0]['content'][0]['text'] ?? '');

        return array_merge($this->_common($field), [
            'type' => 'multiselect',
            'options' => [
                [
                    'value' => $field->checkedValue,
                    'label' => $label
                ]
            ]
        ]);
    }

    // ================================================================================ //

    /**
     * Get the JSON representation of a Formie file upload field.
     *
     * @param $field
     * @return array
     */
    private function _fileUpload($field): array
    {
        return array_merge($this->_common($field), [
            'type' => 'file',
        ]);
    }

}
