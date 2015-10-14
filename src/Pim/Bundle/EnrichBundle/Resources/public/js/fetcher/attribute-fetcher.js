'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function ($, _, BaseFetcher, Routing) {
    return BaseFetcher.extend({
        identifierPromise: null,

        /**
         * Return the identifier attribute
         *
         * @returns {Promise}
         */
        getIdentifierAttribute: function () {
            if (null !== this.identifierPromise) {
                return this.identifierPromise;
            }

            return this.fetchByTypes([this.options.identifier_type])
                .then(function (attributes) {
                    if (0 < attributes.length) {
                        this.identifierPromise = $.Deferred().resolve(attributes[0]);

                        return this.identifierPromise;
                    }

                    return $.Deferred()
                        .reject()
                        .promise();
                }.bind(this));
        },

        /**
         * Fetch attributes by types
         *
         * @param {Array} attributeTypes
         *
         * @return {Promise}
         */
        fetchByTypes: function (attributeTypes) {
            return $.getJSON(Routing.generate(this.options.urls.list, { types: attributeTypes.join(',') }))
                .then(_.identity)
                .promise();
        },

        /**
         * {@inheritdoc}
         */
        clear: function () {
            BaseFetcher.prototype.clear.apply(this, arguments);

            this.identifierPromise = null;
        }
    });
});
