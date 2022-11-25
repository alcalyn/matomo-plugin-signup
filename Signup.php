<?php

namespace Piwik\Plugins\Signup;

use Piwik\Nonce;
use Piwik\Plugin;
use Piwik\View;

/**
 * Add ability to anonymous users to signup
 * and create a new site to track.
 */
class Signup extends Plugin
{
    /**
     * @var bool Whether the next api call requires password confirmation.
     *           Used to signup as anonymous.
     */
    public static $disablePasswordConfirmationOnce = false;

    /**
     * @var bool
     */
    private $allowNewUsersToSignup;

    /**
     * {@InheritDoc}
     */
    public function __construct($pluginName = false)
    {
        parent::__construct($pluginName);

        $settings = new SystemSettings();
        $this->allowNewUsersToSignup = $settings->allowNewUsersToSignup->getValue();
    }

    /**
     * @see \Piwik\Plugin::registerEvents()
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Template.loginNav' => 'addSignupButton',
            'Template.beforeContent' => 'addSignupForms',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Login.userRequiresPasswordConfirmation' => 'userRequiresPasswordConfirmation',
        );
    }


    /**
     * Listener to disable password confirmation once,
     * to allow to signup as anonymous from api.
     */
    public function userRequiresPasswordConfirmation(&$requiresPasswordConfirmation, $login)
    {
        if (self::$disablePasswordConfirmationOnce) {
            $requiresPasswordConfirmation = false;
            self::$disablePasswordConfirmationOnce = false;
        }
    }

    /**
     * Inject signup button in login form.
     *
     * @param string $html
     * @param string $position
     */
    public function addSignupButton(&$html, $position)
    {
        if ('bottom' !== $position) {
            return;
        }

        $view = new View('@Signup/_signup-button');
        $view->allowNewUsersToSignup = $this->allowNewUsersToSignup;

        $html .= $view->render();
    }

    /**
     * Inject forms needed for user and site creation
     * in login page. Forms are hidden at the beginning.
     *
     * @param string $html
     * @param string $page
     */
    public function addSignupForms(&$html, $page)
    {
        if ('login' !== $page) {
            return;
        }

        if (!$this->allowNewUsersToSignup) {
            return;
        }

        $this->injectCreateUserForm($html, $page);
        $this->injectCreateSiteForm($html, $page);
    }

    /**
     * Inject user creation form.
     *
     * @param string $html
     * @param string $page
     */
    public function injectCreateUserForm(&$html, $page)
    {
        $view = new View('@Signup/_create-user-form');

        $view->addForm(new FormSignup());
        $view->nonce = Nonce::getNonce('Signup.signup');

        $html .= $view->render();
    }

    /**
     * Inject site creation form.
     *
     * @param string $html
     * @param string $page
     */
    public function injectCreateSiteForm(&$html, $page)
    {
        $view = new View('@Signup/_create-site-form');

        $html .= $view->render();
    }

    /**
     * Return list of plug-in specific JavaScript files to be imported by the asset manager.
     *
     * @param array $jsFiles
     *
     * @see \Piwik\AssetManager
     */
    public function getJsFiles(&$jsFiles)
    {
        if (!$this->allowNewUsersToSignup) {
            return;
        }

        $jsFiles[] = 'plugins/Signup/javascripts/signup.js';
    }

    /**
     * Return list of plug-in specific CSS files to be imported by the asset manager.
     *
     * @param array $stylesheetFiles
     *
     * @see \Piwik\AssetManager
     */
    public function getStylesheetFiles(&$stylesheetFiles)
    {
        $stylesheetFiles[] = 'plugins/Signup/stylesheets/signup.less';
    }

    /**
     * Return list of plug-in specific translations keys needed in Javascript.
     *
     * @param array $translationKeys
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Installation_PasswordDoNotMatch';
        $translationKeys[] = 'Installation_SetupWebsiteSetupSuccess';
        $translationKeys[] = 'Signup_CancelSiteConfirm';
    }
}
