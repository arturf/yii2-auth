<?php
/**
 * AuthModule class file.
 * @author Artur Fursa <art@binn.ru>
 * @author Yevhen Servetnyk <evgeniy.servetnik@binn.ru>
 * @copyright Copyright &copy; Binn Ltd. 2014-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace auth;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Web module for managing Yii's built-in authorization manager (\yii\rbac\DbeManager).
 */
class Module extends \yii\base\Module
{
    /**
     * @var string Name of the user model class. Change this if your user model name is different than the default value.
     */
    public $userClass;

    /**
     * @var string Name of the user id column. Change this if the id column in your user table is different than the default value.
     */
    public $userIdColumn = 'id';

    /**
     * @var string Name of the user name column. Change this if the name column in your user table is different than the default value.
     */
    public $userNameColumn = 'name';

    /**
     * @var array Map of flash message keys to use for the module.
     */
    public $flashKeys = array();

    /**
     * @var string String the id of the default controller for this module.
     */
    public $defaultRoute = 'assignment';

    /**
     * @var array Map of application's controllers. For example,
     *
     * ```php
     * 'applicationControllers' => [
     *      '\frontend\controllers' => Yii::getAlias('@frontend') . '/controllers',
     *      '\backend\controllers' => Yii::getAlias('@backend') . '/controllers',
     * ],
     * ```
     */
    public $applicationControllers = [];

    /**
     * @var array List of rbac roles that don't needs check rights. Be careful with this.
     */
    public $admins = ['admin'];
    
    /**
     * @var array Access filter configuration for module's actions. For example,
     *
     * ```php
     * 'accessFilterBehavior' => [
     *     'class' => yii\filters\AccessControl::className(),
     *     'rules' => [
     *         [
     *             'allow' => true,
     *             'actions' => ['index'],
     *         ],
     *     ],
     * ],
     *             
     * If not specified (and user is not admin) all actions will be filters by permission name like `auth.controller_id.action_id`
     */
    public $accessFilterBehavior;

    /**
     * @var string Behavior for manager component
     */
    public $authBehavior = 'auth\components\AuthBehavior';

    /**
     * @var boolean Use application prefix for generated permission names
     */
    public $useApplicationPrefix = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->registerTranslations();

        if (empty($this->userClass)) {
            $this->userClass = Yii::$app->user->identityClass;
        }

        $this->flashKeys = array_merge($this->flashKeys, [
            'error' => 'error',
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
        ]);
    }

    /**
     * Register i18n messages.
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['auth.*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@vendor/binn/yii2-auth/messages',
            'fileMap' => [
                'auth.main' => 'main.php'
            ],
        ];
    }

    /**
     * Returns the module version number.
     *
     * @return string Version number.
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * Check that current user can administrate this module.
     * 
     * @param \yii\base\Action $action
     *
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isAdmin() && !empty($this->accessFilterBehavior)) {
            $action->controller->attachBehavior('accessFilter', $this->accessFilterBehavior);
        } elseif (!Yii::$app->user->isAdmin() && !Yii::$app->authManager->checkRoute()) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }

        return parent::beforeAction($action);
    }
}
