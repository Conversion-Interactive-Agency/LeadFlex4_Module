<?php
/**
 * Leadflex plugin for Craft CMS 3.x
 *
 * This is a generic Craft CMS plugin
 *
 * @link      conversionia.com
 * @copyright Copyright (c) 2023 Jeff Benusa
 */

namespace conversionia\leadflex\console\controllers;

use conversionia\leadflex\Leadflex;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * User Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft leadflex/disable-user
 *
 * Actions must be in 'kebab-case' so actionDoSomething() maps to 'do-something',
 * and would be invoked via:
 *
 * ./craft leadflex/disable-user/do-something
 *
 * @author    Jeff Benusa
 * @package   Leadflex
 * @since     1.0.0
 */
class UsersController extends Controller
{
    // Public Methods
    // =========================================================================
    public $email;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['email']);
    }
    /**
     * Handle leadflex/users console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionIndex($email)
    {
        echo 'Hello world from the UserController';
        return;
    }

    /**
     * Handle leadflex/users/disable-user console commands
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @return mixed
     */
    public function actionDisableUser($email)
    {
        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($email);
        if (!$user) {
            echo "User not found: $email\n";
            return false;
        }

        $usersService = Craft::$app->getUsers();
        $usersService->suspendUser($user);
        echo "User successfully suspended: $email\n";

        return $email;
    }
}
