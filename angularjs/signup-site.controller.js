/**
 * AngularJs controller for site creation.
 * Minimalist version of 'SitesManagerController' and 'SitesManagerSiteController' controllers in SitesManager plugin.
 * Though, this controller reuse the same 'site-fields.html' template in SitesManager plugin.
 */
(function (angular, document) {
    angular.module('piwikApp').controller('SignupSiteController', SignupSiteController);

    SignupSiteController.$inject = ['$scope', '$filter', 'piwikApi', '$timeout', 'sitesManagerAPI', 'sitesManagerAdminSitesModel', 'piwik'];

    function SignupSiteController($scope, $filter, piwikApi, $timeout, sitesManagerAPI, sitesManagerAdminSitesModel, piwik) {

        var translate = $filter('translate');

        $scope.site = {};
        $scope.visible = false;

        $scope.showSignupSiteForm = function () {
            angular.element('.loginForm').parent().append(angular.element('.signupSiteForm'));
            $scope.visible = true;
        };

        $scope.hideSignupSiteForm = function () {
            $scope.visible = false;
        };

        $scope.cancelEditSite = function () {
            if (confirm(translate('Signup_CancelSiteConfirm'))) {
                backToLogin();
            }
        };

        var updateView = function () {
            $timeout(function () {
                $('.editingSite').find('select').material_select();
                Materialize.updateTextFields();
            });
        }

        var init = function () {
            $scope.period = piwik.broadcast.getValueFromUrl('period');
            $scope.date = piwik.broadcast.getValueFromUrl('date');
            $scope.adminSites = sitesManagerAdminSitesModel;
            $scope.hasSuperUserAccess = false;
            $scope.cacheBuster = piwik.cacheBuster;

            initCurrencyList();
            initTimezones();

            initModel();
            initActions();

            $scope.site.isLoading = true;

            piwikApi.post({method: 'Signup.getAnonymousAvailableMeasurableTypes'}).then(function (types) {
                $scope.site.isLoading = false;
                var type = types[0];

                if (type) {
                    $scope.currentType = type;
                    $scope.howToSetupUrl = type.howToSetupUrl;
                    $scope.isInternalSetupUrl = '?' === ('' + type.howToSetupUrl).substr(0, 1);
                    $scope.typeSettings = type.settings;

                    $scope.measurableSettings = angular.copy(type.settings);
                } else {
                    $scope.currentType = {name: $scope.site.type};
                }
            });
        };

        var initActions = function () {
            $scope.editSite = editSite;
            $scope.saveSite = saveSite;
            $scope.openDeleteDialog = function () {};
            $scope.site['delete'] = function () {};
        };

        var initModel = function() {
            initNewSite();
            $scope.site.removeDialog = {};
            updateView();
        };

        var editSite = function () {
            $scope.site.editMode = true;
            $scope.measurableSettings = [];
        };

        var saveSite = function() {
            var values = {
                siteName: $scope.site.name,
                timezone: $scope.site.timezone,
                currency: $scope.site.currency,
                type: $scope.site.type,
                settingValues: {}
            };

            angular.forEach($scope.measurableSettings, function (settings) {
                if (!values['settingValues'][settings.pluginName]) {
                    values['settingValues'][settings.pluginName] = [];
                }

                angular.forEach(settings.settings, function (setting) {
                    var value = setting.value;
                    if (value === false) {
                        value = '0';
                    } else if (value === true) {
                        value = '1';
                    }
                    if (angular.isArray(value) && setting.uiControl == 'textarea') {
                        var newValue = [];
                        angular.forEach(value, function (val) {
                            // as they are line separated we cannot trim them in the view
                            if (val) {
                                newValue.push(val);
                            }
                        });
                        value = newValue;
                    }

                    values['settingValues'][settings.pluginName].push({
                        name: setting.name,
                        value: value
                    });
                });
            });

            piwikApi.post({method: 'Signup.signupSite'}, values).then(function (response) {
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                var message = translate('Installation_SetupWebsiteSetupSuccess', $scope.site.name);

                notification.show(message, {context: 'success', id: 'websitecreated'});
                notification.scrollToNotification();

                backToLogin();
            });
        };

        var initNewSite = function() {
            $scope.site.editMode = true;

            if ($scope.typeSettings) {
                // we do not want to manipulate initial type settings
                $scope.measurableSettings = angular.copy($scope.typeSettings);
            }
        };

        var backToLogin = function () {
            $scope.visible = false;
            document.dispatchEvent(new Event('signup_site_created'));
        };

        var initTimezones = function() {

            sitesManagerAPI.getTimezonesList(

                function (timezones) {

                    var scopeTimezones = [];
                    $scope.timezones = [];

                    angular.forEach(timezones, function(groupTimezones, timezoneGroup) {

                        angular.forEach(groupTimezones, function(label, code) {

                            scopeTimezones.push({
                                group: timezoneGroup,
                                key: code,
                                value: label
                            });
                        });
                    });

                    $scope.timezones = scopeTimezones;
                }
            );
        };

        var initCurrencyList = function () {

            sitesManagerAPI.getCurrencyList(function (currencies) {
                $scope.currencies = currencies;
            });
        };

        init();
        editSite();
    }
})(angular, document);
