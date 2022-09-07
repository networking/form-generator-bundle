define([
    "jquery", "ckeditor","underscore", "backbone", "collections/snippets", "collections/my-form-snippets", "views/my-form", "models/my-form"
], function ($, CKEDITOR, _, Backbone, SnippetsCollection, MyFormSnippetsCollection, MyFormView, MyFormModel) {
    return {
        initialize: function (globals) {
            var formId = globals.id;
            var url = globals.url;
            var backToListUri = globals.backToListUri;
            var formModel = new MyFormModel({id: formId, url: url});
            if (formId != null && formId != "undefined") {
                formModel.fetch({success: function (model) {
                        let formFieldCollection = model.get('collection');

                        formFieldCollection.forEach((item) => {
                            let fields = item.fields
                            if(!fields.inputsize) {
                                fields.inputsize = {
                                    "label": "Input Size",
                                    "type": "select",
                                    "value": [
                                        {
                                            "value": "",
                                            "label": "Default",
                                            "css_config": "default",
                                            "selected": true
                                        },
                                        {
                                            "value": "col-md-2",
                                            "label": "Mini",
                                            "css_config": "xs",
                                            "selected": false
                                        },
                                        {
                                            "value": "col-md-4",
                                            "label": "Small",
                                            "css_config": "s",
                                            "selected": false
                                        },
                                        {
                                            "value": "col-md-6",
                                            "label": "Medium",
                                            "css_config": "m",
                                            "selected": false
                                        },
                                        {
                                            "value": "col-md-8",
                                            "label": "Large",
                                            "css_config": "l",
                                            "selected": false
                                        },
                                        {
                                            "value": "col-md-10",
                                            "label": "Xlarge",
                                            "css_config": "xl",
                                            "selected": false
                                        },
                                        {
                                            "value": "col-md-12",
                                            "label": "Xxlarge",
                                            "css_config": "xxl",
                                            "selected": false
                                        }
                                    ]

                                }
                            }
                        })

                        var collection = new MyFormSnippetsCollection(formFieldCollection);
                        new MyFormView({
                            model: model, collection: collection, backToListUri: backToListUri
                        });
                    }
                });
            } else {
                // Bootstrap "My Form" with 'Form Name' snippet.
                new MyFormView({
                    model: formModel, collection: new MyFormSnippetsCollection(), backToListUri: backToListUri
                });
            }
        }
    }
});
