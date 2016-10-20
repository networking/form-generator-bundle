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
                        var collection = new MyFormSnippetsCollection(model.get('collection'));
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
