require.config({
    baseUrl: "/bundles/networkingformgenerator/assets/js/lib/",
    shim: {
        'backbone': {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        'underscore': {
            exports: '_'
        },
        'bootstrap': {
            deps: ['jquery'],
            exports: '$.fn.popover'
        },
        'ckeditor': {
            exports: 'CKEDITOR'
        }

    },
    paths: {
        app: "..",
        ckeditor: 'ckeditor/ckeditor',
        collections: "../collections",
        data: "../data",
        models: "../models",
        helper: "../helper",
        templates: "../templates",
        views: "../views"
    }
});
require([
    'app/app', 'globals', 'config'], function (app, globals) {
    app.initialize(globals);

});
