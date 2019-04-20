<?php

namespace Piwik\Plugins\Signup;

use Piwik\Piwik;
use Piwik\QuickForm2;

/**
 * Form used to signup as new user.
 */
class FormSignup extends QuickForm2
{
    /**
     * {@InheritDoc}
     */
    function __construct($id = 'signup_form', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        $attributes = array_merge($attributes ?: [], ['action' => '?module=Signup']);
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    /**
     * {@InheritDoc}
     */
    function init()
    {
        $this->addElement('text', 'login')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Login_LoginOrEmail')));

        $this->addElement('password', 'password')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('General_Password')));

        $this->addElement('text', 'email')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_Email')));

        $this->addElement('hidden', 'nonce');

        $this->addElement('submit', 'submit');
    }
}
