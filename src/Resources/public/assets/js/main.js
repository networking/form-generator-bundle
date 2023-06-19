require.config({
    baseUrl: "/bundles/",
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
        },
        "jqueryFormSerializer": ["jquery"],
        "confirmExit": ["jquery"]

    },
    paths: {
        jquery: "networkingformgenerator/assets/vendor/jquery/dist/jquery.min",
        app: "networkingformgenerator/assets/js",
        ckeditor: 'fosckeditor/ckeditor',
        jqueryFormSerializer: "networkingformgenerator/assets/js/lib/jqueryFormSerializer",
        collections: "networkingformgenerator/assets/js/collections",
        data: "networkingformgenerator/assets/js/data",
        models: "networkingformgenerator/assets/js/models",
        helper: "networkingformgenerator/assets/js/helper",
        templates: "networkingformgenerator/assets/js/templates",
        views: "networkingformgenerator/assets/js/views",
        underscore: "networkingformgenerator/assets/js/lib/underscore",
        backbone: "networkingformgenerator/assets/js/lib/backbone",
        globals: "networkingformgenerator/assets/js/lib/globals",
        config: "networkingformgenerator/assets/js/lib/config",
        bootstrap: "networkingformgenerator/assets/js/lib/bootstrap",
        text: "networkingformgenerator/assets/js/lib/text"
    }
});
require([
    'app/app', 'globals', 'config'], function (app, globals) {
    app.initialize(globals);

});
