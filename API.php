<?php

namespace Piwik\Plugins\Signup;

use Piwik\Auth;
use Piwik\Nonce;
use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\API\API as PluginApi;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;

/**
 * API for plugin Signup
 *
 * @method static \Piwik\Plugins\Signup\API getInstance()
 */
class API extends PluginApi
{
    /**
     * Public API, returns non-sensitive settings about Signup system settings.
     * Can be accessed by requesting:
     *
     * ?module=API&method=Signup.getSignupPublicSettings&format=json2
     *
     * @return array Non-sensitive settings of Signup plugin.
     */
    public function getSignupPublicSettings()
    {
        $settings = new SystemSettings();

        return [
            'signupPlugin' => true,
            'allowNewUsersToSignup' => $settings->allowNewUsersToSignup->getValue(),
        ];
    }

    /**
     * Endpoint to signup a new user.
     *
     * @param string $login
     * @param string $password
     * @param string $email
     * @param string $nonce Generated from FormSignup.
     *
     * @return array In case of success, returns token_auth from created user, needed for signupSite().
     *
     * @throws Exception When signup are disabled in settings.
     */
    public function signupUser($login, $password, $email, $nonce)
    {
        $this->mustUsersSignupAllowed();

        $form = new FormSignup();

        if (!$form->validate()) {
            return [
                'result' => 'error',
                'reason' => 'form_invalid',
                'form_errors' => $form->getFormData()['errors'],
            ];
        }

        if (!Nonce::verifyNonce('Signup.signup', $nonce)) {
            return [
                'result' => 'error',
                'reason' => 'nonce_invalid',
            ];
        }

        Access::getInstance()->doAsSuperUser(function () use ($login, $password, $email) {
            UsersManagerApi::getInstance()->addUser($login, $password, $email);
        });

        return [
            'result' => 'success',
            'token_auth' => UsersManagerApi::getInstance()
                ->createAppSpecificTokenAuth($login, $password, 'SignupPlugin')
            ,
        ];
    }

    /**
     * Create a site and give the access rights
     * to the user behind $token_auth.
     *
     * @param string $token_auth
     * @param string $siteName
     *
     * @return array In case of success: {"result":"success","message":"ok"}
     *
     * @throws Exception When signup are disabled in settings.
     */
    public function signupSite(
        $token_auth,
        $siteName,
        $urls = null,
        $ecommerce = null,
        $siteSearch = null,
        $searchKeywordParameters = null,
        $searchCategoryParameters = null,
        $excludedIps = null,
        $excludedQueryParameters = null,
        $timezone = null,
        $currency = null,
        $group = null,
        $startDate = null,
        $excludedUserAgents = null,
        $keepURLFragments = null,
        $type = null,
        $settingValues = null,
        $excludeUnknownUrls = null
    ) {
        $this->mustUsersSignupAllowed();

        $auth = StaticContainer::get(Auth::class);
        $auth->setTokenAuth($token_auth);
        $authResult = $auth->authenticate();

        return Access::getInstance()->doAsSuperUser(function () use (
            $authResult,
            $siteName,
            $urls,
            $ecommerce,
            $siteSearch,
            $searchKeywordParameters,
            $searchCategoryParameters,
            $excludedIps,
            $excludedQueryParameters,
            $timezone,
            $currency,
            $group,
            $startDate,
            $excludedUserAgents,
            $keepURLFragments,
            $type,
            $settingValues,
            $excludeUnknownUrls
        ) {
            $idSite = SitesManagerApi::getInstance()->addSite(
                $siteName,
                $urls,
                $ecommerce,
                $siteSearch,
                $searchKeywordParameters,
                $searchCategoryParameters,
                $excludedIps,
                $excludedQueryParameters,
                $timezone,
                $currency,
                $group,
                $startDate,
                $excludedUserAgents,
                $keepURLFragments,
                $type,
                $settingValues,
                $excludeUnknownUrls
            );

            return UsersManagerApi::getInstance()->setUserAccess($authResult->getIdentity(), 'admin', $idSite);
        });
    }

    /**
     * Returns all available measurable types.
     *
     * 'Alias' to Piwik\Plugins\API\API::getAvailableMeasurableTypes
     * but allows anonymous users to call it,
     * because when creating a site, user has not yet any rights.
     *
     * @deprecated It calls another deprecated API endpoint.
     *
     * @return array
     */
    public function getAnonymousAvailableMeasurableTypes()
    {
        return Access::getInstance()->doAsSuperUser(function () {
            return $this->getAvailableMeasurableTypes();
        });
    }

    /**
     * Check if superuser has allowed new users to signup in system settings.
     *
     * @throws Exception When signup are disabled in settings.
     */
    private function mustUsersSignupAllowed()
    {
        $settings = new SystemSettings();
        $allowedToSignup = $settings->allowNewUsersToSignup->getValue();

        if (!$allowedToSignup) {
            throw new \Exception('Signup are disabled on this instance.');
        }
    }
}
