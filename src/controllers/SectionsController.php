<?php
/**
 * Business Logic module for Craft CMS 3.x
 *
 * LeadFlex logic needs to be transfered
 *
 * @link      conversionia.com
 * @copyright Copyright (c) 2022 Benusa
 */

namespace conversionia\leadflex\controllers;

use Craft;
use craft\helpers\StringHelper;
use craft\web\Controller;

use yii\web\Response;

/**
 * Controller
 * https://nystudio107.com/blog/using-the-craft-cms-graphql-api-on-the-frontend
 */
class SectionsController extends Controller
{

    const GQL_TOKEN_NAME = 'Hireline';

    // Protected Properties
    // =========================================================================

    //    protected $allowAnonymous = [
    //        'get-csrf',
    //        'get-gql-token',
    //        'get-field-options',
    //    ];

    /* todo - remove anonymous access; Delete $allowAnonymous = true; Uncomment protected $allowAnonymous above */

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        // Disable CSRF validation for get-csrf POST requests
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Return all the field options from the passed in array of $fieldHandles
     *
     * @return Response
     */
    public function actionGetFieldOptions(): Response
    {
        $result = [];
        $request = Craft::$app->getRequest();
        $fieldHandles = $request->getBodyParam('fieldHandles');
        foreach ($fieldHandles as $fieldHandle) {
            $field = Craft::$app->getFields()->getFieldByHandle($fieldHandle);
            if ($field) {
                $result[$fieldHandle] = $field->options;
            }
        }

        return $this->asJson($result);
    }

    public function actionGetSectionFields(): Response
    {
        $result = [];
        $request = Craft::$app->getRequest();
        $sectionHandle = $request->getBodyParam('sectionHandle');
        $section = Craft::$app->getSections()->getSectionByHandle($sectionHandle);
        $entryTypes = $section->getEntryTypes();
        foreach ($entryTypes as $entryType) {
            $typeFields = $entryType->fieldLayout->fields;
            $fieldResponse = [];
            foreach ($typeFields as $field) {
                $type = StringHelper::slugify($field->displayName());
                $class = get_class($field);
                $fieldResponse = (object) array_merge( (array)$field, array( 'type' => $type, 'class' => $class ));
                $result[$entryType->handle][$field->handle] = $fieldResponse;
            }
        }

        return $this->asJson($result);
    }

    public function actionSpeak(): Response
    {
        return $this->asJson('Hello World');;
    }
}
