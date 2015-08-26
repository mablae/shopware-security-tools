;
(function ($, window) {
    'use strict';

    $.plugin('mwstPasswordStrength', {

        /**
         * Default plugin initialisation function.
         * Registers all needed event listeners
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;
            console.log(me);
            me.password = me.$el.parents('form').find('.password');

            me._on(me.password, 'keyup', function () {
                me.updatePasswordStrengthBar(me.password.val(), me);
            });
            me._on(me.password, 'change', function () {
                me.updatePasswordStrengthBar(me.password.val(), me);
            });
            me._on(me.password, 'paste', function () {
                me.updatePasswordStrengthBar(me.password.val(), me);
            });
        },
        updatePasswordStrengthBar: function (password, plugin) {
            var score = plugin.getScore(password);
            var strength = 0;

            console.log(score);

            if (score > 86) {
                strength = 3;
            } else if (score > 60) {
                strength = 2;
            } else if (score > 25) {
                strength = 1
            }

            plugin.$el.removeClass('mwst--password--strength--0');
            plugin.$el.removeClass('mwst--password--strength--1');
            plugin.$el.removeClass('mwst--password--strength--2');
            plugin.$el.removeClass('mwst--password--strength--3');
            plugin.$el.addClass('mwst--password--strength--' + strength.toString());
        },
        getScore: function (password) {
            var minChars = 8;
            var eachCharSignificance = 4;
            var maxCharsSignificance = minChars * eachCharSignificance;

            var bigAndSmallCharsSignificance = 8;
            var specialCharSignificance = 8;
            var numberSignificance = 8;

            var total = eachCharSignificance * minChars + bigAndSmallCharsSignificance +
                specialCharSignificance + numberSignificance;

            var countCharacters = password.length;

            var passwordSignificance = countCharacters * eachCharSignificance;
            if (passwordSignificance > maxCharsSignificance) {
                passwordSignificance = maxCharsSignificance;
            }

            // Checks the amount of characters first.
            // If there are less than 8 characters, the bar should not fill over 50%
            if (countCharacters >= minChars) {

                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
                    passwordSignificance += bigAndSmallCharsSignificance;

                }

                if (password.match(/[!#\$%\*\+,\-\.;\/\[\]_:]/)) {
                    passwordSignificance += specialCharSignificance;
                }

                if (password.match(/[0-9]/)) {
                    passwordSignificance += numberSignificance;

                }
            }

            return Math.round((passwordSignificance / total) * 100);

        }
    });

    $('.mwst--password--strength--bar--container').mwstPasswordStrength();
})(jQuery, window);

