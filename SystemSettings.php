<?php

namespace Piwik\Plugins\Signup;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Piwik;

/**
 * Defines Settings for Signup.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /**
     * @var Setting Whether users signup are allowed or not.
     */
    public $allowNewUsersToSignup;

    /**
     * Init Signup plugin system settings.
     *
     * {@InheritDoc}
     */
    protected function init()
    {
        $this->allowNewUsersToSignup = $this->createAllowNewUsersToSignupSetting();
    }

    private function createAllowNewUsersToSignupSetting()
    {
        return $this->makeSetting('allowNewUsersToSignup', $default = true, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('Signup_AllowNewUsersToSignup');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('Signup_AllowNewUsersToSignupDescription');
        });
    }
}
