define([
    "jquery", "ckeditor", "underscore", "backbone", "collections/snippets", "views/temp-snippet", "helper/pubsub", "text!templates/app/renderform.html", "text!templates/app/form.html", "text!templates/app/message.html"
    , "views/tab", "text!data/input_limited.json", "text!data/radio.json", "text!data/select.json", "text!data/buttons.json", "text!templates/app/render.html"
], function ($, CKEDITOR,  _, Backbone, SnippetsCollection, TempSnippetView, PubSub, _renderForm, _form, __message, TabView, inputJSON, radioJSON, selectJSON, buttonsJSON, renderTab) {
    return Backbone.View.extend({
        template: _form, events: {
            'click button#saveForm': 'saveForm',
            'change input#formName': 'updateName',
            'change textarea#infoText': 'updateInfoText',
            'change textarea#thankYouText': 'updateThankYouText',
            'change input#email': 'updateEmail',
            'change select#action': 'updateAction',
            'change input#redirect': 'updateRedirect',
            'change input#emailField': 'updateEmailField'
        }, initialize: function (options) {
            this.collection.on("add", this.render, this);
            this.collection.on("remove", this.render, this);
            this.collection.on("change", this.render, this);
            PubSub.on("mySnippetDrag", this.handleSnippetDrag, this);
            PubSub.on("tempMove", this.handleTempMove, this);
            PubSub.on("tempDrop", this.handleTempDrop, this);
            PubSub.on("saveForm", this.saveForm, this);
            this.$build = $("#build");
            var that = this;
            this.model.on('error', function (model, errors) {
                that.showErrors(errors);
                $('button#saveForm').attr('disabled', true);
            });
            this.model.on('change', function () {
                if (!that.isModelInvalid()) {
                    that.hideErrors();
                }
            });

            this.confirm = false;
            this.renderForm = _.template(_renderForm);
            this.template = _.template(this.template);
            this.backToListUri = options.backToListUri;
            this.render();


            this.model.view = this;
            this.model.collection = this.collection;



        }, render: function () {

            //Render Snippet Views
            var that = this;
            this.$el.html(this.template({model: that.model.toJSON(), backToListUri: this.backToListUri}));
            if (_.size(this.collection) > 0) {
                _.each(this.collection.renderAll(), function (snippet) {

                    that.$el.find('#collection').append(snippet);
                });
            }

            this.$el.appendTo("#build");
            this.delegateEvents();



            CKEDITOR.config.customConfig = '../networkingformgenerator/assets/js/lib/ckeditor_config.js';

            CKEDITOR.replace( 'infoText',{width:'100%'} );
            CKEDITOR.replace( 'thankYouText',{width:'100%'});


            var module = this;
            for (var i in CKEDITOR.instances) {

                CKEDITOR.instances[i].on('change', function(e) {
                    module.updateInfoText(e);
                    module.updateThankYouText(e);
                });

            }

            $(window).on('beforeunload', function(event) {
                var e = event || window.event, message = window.SONATA_TRANSLATIONS.CONFIRM_EXIT;
                if (module.confirm) {
                    // For old IE and Firefox
                    if (e) {

                        e.returnValue = message;
                    }
                    return message;
                }
            });

            //Bootstrap tabs from json.
            new TabView({
                title: "Input", collection: new SnippetsCollection(JSON.parse(inputJSON))
            });
            new TabView({
                title: "Radios / Checkboxes", collection: new SnippetsCollection(JSON.parse(radioJSON))
            });
            new TabView({
                title: "Select", collection: new SnippetsCollection(JSON.parse(selectJSON))
            });
            //new TabView({
            //    title: "Buttons", collection: new SnippetsCollection(JSON.parse(buttonsJSON))
            //});
            new TabView({
                title: "Rendered", content: renderTab
            });
            $("#render").val(that.renderForm({
                text: _.map(this.collection.renderAllClean(), function (e) {
                    return e.html()
                }).join("\n")
            }));
            $("#components .tab-pane").first().addClass("active");
            $("#formtabs li").first().addClass("active");

        }, getBottomAbove: function (eventY) {
            var myFormBits = $(this.$el.find("#target .component"));
            return _.find(myFormBits, function (renderedSnippet) {
                return ($(renderedSnippet).position().top + $(renderedSnippet).height()) > eventY - 240;
            });


        }, handleSnippetDrag: function (mouseEvent, snippetModel) {
            $("body").append(new TempSnippetView({model: snippetModel}).render());
            this.collection.remove(snippetModel);
            PubSub.trigger("newTempPostRender", mouseEvent);
        }, handleTempMove: function (mouseEvent) {
            this.build = document.getElementById("collection");
            this.buildBCR = this.build.getBoundingClientRect();
            $(".target").removeClass("target");
            if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) &&  mouseEvent.pageY >= this.buildBCR.top ) {
                $(".targetbefore").removeClass("targetbefore");
                $(this.getBottomAbove(mouseEvent.pageY)).addClass("target");
            } else if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) && mouseEvent.pageY <= this.buildBCR.top) {
                $(this.getBottomAbove(mouseEvent.pageY)).addClass("targetbefore");
                $(".target").removeClass("target");
            } else {
                $(".targetbefore").removeClass("targetbefore");
                $(".target").removeClass("target");
            }
        }, handleTempDrop: function (mouseEvent, model) {
            var target = $(".target");
            var targetBefore = $(".targetbefore");

            if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) && mouseEvent.pageY >= this.buildBCR.top ) {
                this.collection.add(model, {at: target.index() + 1});
            } else if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) &&  mouseEvent.pageY <= this.buildBCR.top) {
                this.collection.add(model, {at: targetBefore.index()});
            }

            target.removeClass("target");
            targetBefore.removeClass("targetbefore");
            this.confirm = true;
        }, saveForm: function (event) {
            var btn = $(event.target);
            btn.button('loading');
            var that = this;
            $('#messageBox').html('');
            var module = this;
            this.model.save(this.getModelViewAttr(), {
                success: function (model, xhr) {
                    that.createMessageBox('success', polyglot.t('success'),xhr.message);
                    btn.button('reset');
                    module.confirm = false;
                    $('html, body').animate({
                        scrollTop: $(".initcms").offset().top
                    }, 2000);
                },
                error: function (model, xhr) {
                    var errors = [];
                    if (_.isObject(xhr) && xhr.responseText) {
                        errors = $.parseJSON(xhr.responseText);
                        that.createMessageBox('danger', 'Oh no!','an error has occured, please check your form details');
                    } else {
                        errors = xhr;
                    }

                    that.showErrors(errors);

                    btn.button('reset');
                }
            });
        }, updateName: function (event) {
            var target = $(event.target);
            this.model.set({name: target.val()});
            this.confirm = true;

        }, updateInfoText: function (event) {
            var target = $(event.target);
            this.model.set({info_text: CKEDITOR.instances.infoText.getData()});
            this.confirm = true;
        }, updateThankYouText: function (event) {
            var target = $(event.target);
            this.model.set({thank_you_text: CKEDITOR.instances.thankYouText.getData()});
            this.confirm = true;
        }, updateEmailField: function (event) {
            var target = $(event.target);
            this.model.set({email_field: target.val()});
            this.confirm = true;
        }, updateDoubleOptIn: function (event) {
            var target = $(event.target);
            this.model.set({doubleOptIn: target.val()});
            this.confirm = true;
        }, updateEmail: function (event) {
            var target = $(event.target);
            this.model.set({email: target.val()});
            this.confirm = true;
        }, updateAction: function (event) {
            var target = $(event.target);
            this.model.set({action: target.val()});
            this.confirm = true;
        },  updateRedirect: function (event) {
            var target = $(event.target);
            this.model.set({redirect: target.val()});
            this.confirm = true;
        }, showErrors: function (errors) {
            _.each(errors, function (error) {
                var controlGroup = this.$('.' + error.property_path);
                controlGroup.addClass('has-error');
                controlGroup.find('.help-inline').text(error.message);
            }, this);
        }, hideErrors: function () {
            this.$('.form-group').removeClass('has-error');
            this.$('.help-inline').text('');
            $('button#saveForm').attr('disabled', false);
        }, isModelInvalid: function () {
            return this.model.validate(this.getModelViewAttr());
        }, getModelViewAttr: function () {
            return  {
                name: this.$('input#formName').val(),
                infoText: CKEDITOR.instances.infoText.getData(),
                thankYouText: CKEDITOR.instances.thankYouText.getData(),
                email: this.$('input#email').val(),
                action: this.$('select#action').val(),
                redirect: this.$('input#redirect').val(),
                emailField: this.$('input#emailField').val(),
                doubleOptIn: this.$('input#doubleOptIn').val(),
                collection: this.collection
            };
        }, createMessageBox: function (level, title, message) {
            $('#messageBox').html(_.template(__message, {
                level: level,
                title: title,
                message: message
            }));
        }
    })
});
