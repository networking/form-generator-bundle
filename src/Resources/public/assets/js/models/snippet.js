const TEXT_FIELDS = ['Text Input', 'Password Input', 'Search Input', 'Prepended Text', 'Prepended Icon', 'Appended Text', 'Appended Icon', 'Text Area' ]
define([
    'jquery', 'underscore', 'backbone'
], function($, _, Backbone) {
    return Backbone.Model.extend({

        getValues: function(){
            let hasRequired = TEXT_FIELDS.includes(this.get('title'));
            let values =  _.reduce(this.get("fields"), function(o, v, k){
                if (v["type"] == "select") {
                    o[k] = _.find(v["value"], function(o){return o.selected})["value"];
                } else {
                    o[k]  = v["value"];
                }
                return o;
            }, {});

            if(values.required === undefined && hasRequired) {
                values.required = false;
            }
            return values;
        }
        , idFriendlyTitle: function(){
            return this.get("title").replace(/\W/g,'').toLowerCase();
        }
        , setField: function(name, value) {
            var fields = this.get("fields");
            fields[name]["value"] = value;
            this.set("fields", fields);
        }
    });
});
