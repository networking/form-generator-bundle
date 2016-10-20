define([
    'jquery', 'underscore', 'backbone'
], function ($, _, Backbone) {

    return Backbone.Model.extend({
        idAttribute: "id", defaults: {
            'name': null, id: null, collection: null, backToListUri: null

        }, initialize: function () {
            this.urlRoot = this.attributes.url;
        },validate: function(attributes){
            var errors = [];
            if ( !attributes.name ){
                 errors.push({property_path: 'formName', message: 'Form must have a name'});
            }

            return errors.length > 0 ? errors : false;
        }
    });
});