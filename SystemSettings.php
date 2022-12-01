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
     * @var Setting List of email domains allowed to signup from this plugin. If empty, no restriction.
     */
    public $allowedEmailDomains;

    /**
     * Init Signup plugin system settings.
     *
     * {@InheritDoc}
     */
    protected function init()
    {
        $this->allowNewUsersToSignup = $this->createAllowNewUsersToSignupSetting();
        $this->allowedEmailDomains = $this->createAllowedEmailDomainsSetting();
    }

    private function createAllowNewUsersToSignupSetting()
    {
        return $this->makeSetting('allowNewUsersToSignup', true, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('Signup_AllowNewUsersToSignup');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->description = Piwik::translate('Signup_AllowNewUsersToSignupDescription');
        });
    }

    private function createAllowedEmailDomainsSetting()
    {
        return $this->makeSetting('allowedEmailDomains', [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->title = Piwik::translate('Signup_AllowedEmailDomains');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->description = Piwik::translate('Signup_AllowedEmailDomainsDescription');
            $field->transform = function ($value) {
                if (empty($value)) {
                    return [];
                }

                $domains = array_map('trim', $value);
                $domains = array_filter($domains, 'strlen');
                $domains = array_values($domains);
                return $domains;
            };
        });
    }
}
