define([
    'jquery', 'underscore', 'backbone',
], function ($, _, Backbone) {

    return Backbone.Model.extend({
        idAttribute: "id", defaults: {
            'name': null, id: null, collection: null, backToListUri: null

        }, initialize: function () {
            this.urlRoot = this.attributes.url;
        },validate: function(attributes){
            let errors = [];
            if ( !attributes.name ){
                 errors.push({property_path: 'name', message: polyglot.t('Form must have a name')});
            }
            if(attributes.action !== 'db' && !attributes.email){
            console.table('hello')
                errors.push({property_path: 'email', message: polyglot.t('Please enter email address')});
            }

            return errors.length > 0 ? errors : false;
        }
    });
});