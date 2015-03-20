"use strict";

define([
        'backbone',
        'underscore',
        'pim/form',
        'pim/attribute-group-manager',
        'text!pim/template/product/tab/attribute/attribute-group-selector'
    ],
    function (Backbone, _, BaseForm, AttributeGroupManager, template) {
        return BaseForm.extend({
            tagName: 'ul',
            className: 'nav nav-tabs attribute-group-selector',
            template: _.template(template),
            state: null,
            badges: {},
            events: {
                'click li': 'change'
            },
            initialize: function()
            {
                this.state = new Backbone.Model({});
                this.listenTo(this.state, 'change', this.render);
                this.badges = {};

                return this;
            },
            render: function()
            {
                this.$el.empty();
                this.$el.html(this.template({
                    current: this.state.get('current'),
                    attributeGroups: this.state.get('attributeGroups'),
                    badges: this.badges
                }));

                this.delegateEvents();

                return this;
            },
            updateAttributeGroups: function(product) {
                AttributeGroupManager.getAttributeGroupsForProduct(product)
                    .done(_.bind(function(attributeGroups) {
                        this.state.set('attributeGroups', attributeGroups);

                        if (undefined === this.state.get('current')) {
                            this.state.set('current', _.keys(attributeGroups)[0]);
                        }
                    }, this));
            },
            change: function(event) {
                this.state.set('current', event.currentTarget.dataset.attributeGroup);

                this.getParent().render();
            },
            getCurrent: function()
            {
                return this.state.get('current');
            },
            getCurrentAttributeGroup: function() {
                return this.state.get('attributeGroups')[this.state.get('current')];
            },
            setCurrent: function(current) {
                this.state.set('current', current);
            },
            getAttributeGroups: function() {
                return this.state.get('attributeGroups');
            },
            addToBadge: function(attributeGroup, code) {
                if (!this.badges[attributeGroup]) {
                    this.badges[attributeGroup] = {};
                }
                if (!this.badges[attributeGroup][code]) {
                    this.badges[attributeGroup][code] = 0;
                }

                this.badges[attributeGroup][code]++;

                this.render();
            },
            removeBadge: function(attributeGroup, code) {
                delete this.badges[attributeGroup][code];

                this.render();
            },
            removeBadges: function(code) {
                if (!code) {
                    this.badges = {};
                } else {
                    _.each(this.badges, _.bind(function(badge) {
                        delete badge[code];
                    }, this));
                }

                this.render();
            }
        });
    }
);