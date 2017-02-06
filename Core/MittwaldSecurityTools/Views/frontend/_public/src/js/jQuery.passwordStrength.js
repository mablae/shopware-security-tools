;
(function ($, window) {
    'use strict';

    $.plugin('mwstPasswordStrength', {

        defaults: {

            /**
             * The selector for the criteria list
             *
             * @property criterialist
             * @type {string}
             */
            criterialist: null
        },

        /**
         * Default plugin initialisation function.
         * Registers all needed event listeners
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this,
                opts = me.opts;

            me.applyDataAttributes();

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

            if (plugin.opts.criterialist) {
                var criteriaList = $(plugin.opts.criterialist);

                if(criteriaList){
                    if(this.hasBigSmall(password))
                    {
                        criteriaList.find('.mwst--passwort--criteria--big-small').addClass('check');
                    } else {
                        criteriaList.find('.mwst--passwort--criteria--big-small').removeClass('check');
                    }

                    if(this.hasSpecialChars(password))
                    {
                        criteriaList.find('.mwst--passwort--criteria--special-chars').addClass('check');
                    } else {
                        criteriaList.find('.mwst--passwort--criteria--special-chars').removeClass('check');
                    }

                    if(this.hasNumbers(password))
                    {
                        criteriaList.find('.mwst--passwort--criteria--numbers').addClass('check');
                    } else {
                        criteriaList.find('.mwst--passwort--criteria--numbers').removeClass('check');
                    }
                }
            }
        },
        hasBigSmall: function (password) {
            return password.match(/[a-z]/) && password.match(/[A-Z]/);
        },
        hasSpecialChars: function (password) {
            return password.match(/[\!\#\$%\*\+,\-\.;\/\[\]_:\&\@\§\=]/);
        },
        hasNumbers: function (password) {
            return password.match(/[0-9]/);
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

                if (this.hasBigSmall(password)) {
                    passwordSignificance += bigAndSmallCharsSignificance;
                }

                if (this.hasSpecialChars(password)) {
                    passwordSignificance += specialCharSignificance;
                }

                if (this.hasNumbers(password)) {
                    passwordSignificance += numberSignificance;

                }
            }

            return Math.round((passwordSignificance / total) * 100);

        }
    });

    $('.mwst--password--strength--bar--container').mwstPasswordStrength();
})(jQuery, window);

