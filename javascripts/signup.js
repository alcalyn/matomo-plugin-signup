(function ($, document, ajaxHelper, _pk_translate) {
    $(function () {
        if (document.querySelector('form#signup_form')) {
            placeSignupForm();
            bindSignupButtons();
            reopenLoginOnSignupFinish();
        }
    });

    function bindSignupButtons() {
        document.querySelector('.btn-signup').addEventListener('click', function (e) {
            e.preventDefault();
            hideLoginForm(showSignupForm);
        });

        document.querySelector('#signup_form_nav').addEventListener('click', function (e) {
            e.preventDefault();
            hideSignupForm(showLoginForm);
        });

        document.querySelector('form#signup_form').addEventListener('submit', function (e) {
            e.preventDefault();

            $('.signup-form-error').remove();

            if (!checkPasswordRepeat()) {
                return;
            }

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.useCallbackInCaseOfError();
            ajaxRequest.setLoadingElement('.loadingPiwik');
            ajaxRequest.setFormat('json');
            ajaxRequest.addParams({
                module: 'API',
                method: 'Signup.signupUser',
                format: 'json2',
            }, 'get');
            ajaxRequest.addParams({
                login: $('.signupForm [name="login"]').val(),
                password: $('.signupForm [name="password"]').val(),
                email: $('.signupForm [name="email"]').val(),
                nonce: $('.signupForm [name="nonce"]').val(),
            }, 'post');
            ajaxRequest.setCallback(function (response) {
                if ('success' === response.result) {
                    piwik.token_auth = response.token_auth;

                    hideSignupForm(function () {
                        showSiteForm();
                        angular.element('.signupSiteForm').scope().$apply();
                    });
                } else {
                    handleUserCreationFormErrors(response);
                }
            });
            ajaxRequest.send();
        });

    }

    function handleUserCreationFormErrors(response) {
        if ('form_invalid' === response.reason) {
            for (var property in response.form_errors) {
                if (response.form_errors.hasOwnProperty(property)) {
                    displayFormError(response.form_errors[property], property);
                }
            }
        } else {
            displayFormError(response.message);
        }
    }

    function displayFormError(message, field) {
        var $alert = $('<div class="alert alert-danger signup-form-error">').html(message);

        if (field) {
            $('.signupForm input[name='+field+']').closest('.input-field').append($alert);
        } else {
            $('.message_container').append($alert);
        }
    }

    function checkPasswordRepeat() {
        if ($('.signupForm [name="password"]').val() !== $('.signupForm [name="password_bis"]').val()) {
            displayFormError(_pk_translate('Installation_PasswordDoNotMatch'), 'password_bis');
            return false;
        }

        return true;
    }

    function showLoginForm(callback) {
        $('.loginForm').fadeIn(300, callback);
    }

    function hideLoginForm(callback) {
        $('.loginForm').fadeOut(500, callback);
    }

    function showSignupForm(callback) {
        $('.signupForm').fadeIn(300, callback);
    }

    function hideSignupForm(callback) {
        $('.signupForm').fadeOut(500, callback);
    }

    function showSiteForm() {
        angular.element('.signupSiteForm').scope().showSignupSiteForm();

        $('.loginSection').find('> .col')
            .removeClass('m6 push-m3 l4 push-l4')
            .addClass('l8 push-l2');
    }

    function hideSiteForm() {
        angular.element('.signupSiteForm').scope().hideSignupSiteForm();

        $('.loginSection').find('> .col')
            .removeClass('l8 push-l2')
            .addClass('m6 push-m3 l4 push-l4');
    }

    function placeSignupForm() {
        $('.signupForm').insertAfter('.loginForm');
    }

    function reopenLoginOnSignupFinish() {
        document.addEventListener('signup_site_created', function (e) {
            hideSiteForm();
            showLoginForm();
        }, false);
    }
})(jQuery, document, ajaxHelper, _pk_translate);
