define([
    "jquery", "jqueryFormSerializer", "confirmExit", "ckeditor", "underscore", "backbone", "collections/snippets", "views/temp-snippet", "helper/pubsub", "text!templates/app/form.html", "text!templates/app/message.html"
    , "views/tab", "text!data/input_limited.json", "text!data/radio.json", "text!data/select.json", "text!data/buttons.json"
], function ($, jqueryFormSerializer, confirmExit, CKEDITOR, _, Backbone, SnippetsCollection, TempSnippetView, PubSub, _form, __message, TabView, inputJSON, radioJSON, selectJSON, buttonsJSON) {

    return Backbone.View.extend({
        template: _form, events: {
            'click button#saveForm': 'saveForm',
        },
        initialize: function (options) {
            this.collection.on("add", this.render, this);
            this.collection.on("remove", this.render, this);
            this.collection.on("change", this.render, this);
            PubSub.on("mySnippetDrag", this.handleSnippetDrag, this);
            PubSub.on("tempMove", this.handleTempMove, this);
            PubSub.on("tempDrop", this.handleTempDrop, this);
            PubSub.on("saveForm", this.saveForm, this);
            this.$build = $("#build");
            this.$uniqId = $("#build").data('uniqId');


            $('button#saveForm').on('click', (event) => {
                PubSub.trigger('saveForm', event)
            })
            this.model.on('error', (model, errors) => {

                this.showErrors(errors);
            });

            this.model.on('change', () => {
                if (!this.isModelInvalid()) {
                    this.hideErrors();
                    document.querySelector('button#saveForm').removeAttribute('disabled')
                }
            });
            this.template = _.template(this.template);
            this.backToListUri = options.backToListUri;
            this.render();
            this.model.view = this;
            this.model.collection = this.collection;


        },
        render: function () {
            //Render Snippet Views
            var that = this;
            this.$el.html(this.template({model: that.model.toJSON(), backToListUri: this.backToListUri}));
            if (_.size(this.collection) > 0) {
                _.each(this.collection.renderAll(), function (snippet) {

                    that.$el.find('#collection').append(snippet);
                });
            }

            this.$el.prependTo("#build");
            this.delegateEvents();


            CKEDITOR.config.customConfig = '../networkingformgenerator/assets/js/lib/ckeditor_config.js';

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

            $("#components .tab-pane").first().addClass("active");
            $("#formtabs li").first().addClass("active");

        },
        getBottomAbove: function (eventY) {
            var myFormBits = $(this.$el.find("#target .component"));
            return _.find(myFormBits, function (renderedSnippet) {
                return ($(renderedSnippet).position().top + $(renderedSnippet).height()) > eventY - 240;
            });
        },
        handleSnippetDrag: function (mouseEvent, snippetModel) {
            $("body").append(new TempSnippetView({model: snippetModel}).render());
            this.collection.remove(snippetModel);
            PubSub.trigger("newTempPostRender", mouseEvent);
        },
        handleTempMove: function (mouseEvent) {
            this.build = document.getElementById("collection");
            this.buildBCR = this.build.getBoundingClientRect();
            $(".target").removeClass("target");
            if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) && mouseEvent.pageY >= this.buildBCR.top) {
                $(".targetbefore").removeClass("targetbefore");
                $(this.getBottomAbove(mouseEvent.pageY)).addClass("target");
            } else if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) && mouseEvent.pageY <= this.buildBCR.top) {
                $(this.getBottomAbove(mouseEvent.pageY)).addClass("targetbefore");
                $(".target").removeClass("target");
            } else {
                $(".targetbefore").removeClass("targetbefore");
                $(".target").removeClass("target");
            }
        },
        handleTempDrop: function (mouseEvent, model) {
            var target = $(".target");
            var targetBefore = $(".targetbefore");

            if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) && mouseEvent.pageY >= this.buildBCR.top) {
                this.collection.add(model, {at: target.index() + 1});
            } else if (mouseEvent.pageX >= this.buildBCR.left && mouseEvent.pageX < (this.$build.width() + this.buildBCR.left) && mouseEvent.pageY <= this.buildBCR.top) {
                this.collection.add(model, {at: targetBefore.index()});
            }

            target.removeClass("target");
            targetBefore.removeClass("targetbefore");
        },
        saveForm: function (event) {
            var btn = $(event.target);
            btn.button('loading');
            var that = this;
            var module = this;
            this.model.save(this.getModelViewAttr(), {
                success: function (model, xhr) {
                    that.createMessageBox('success', polyglot.t('success'), xhr.message);
                    btn.button('reset');
                    $('html, body').animate({
                        scrollTop: $(".initcms").offset().top
                    }, 2000);

                    $('.sonata-ba-form form').each(function() {
                        $(this).confirmExit();
                    });
                },
                error: function (model, xhr) {
                    var errors = [];
                    if (_.isObject(xhr) && xhr.responseText) {
                        errors = $.parseJSON(xhr.responseText);

                        that.createMessageBox('danger', 'Oh no!', 'an error has occured, please check your form details');
                    } else {
                        errors = xhr;
                    }

                    $('html, body').animate({
                        scrollTop: $(".initcms").offset().top
                    }, 2000);

                    that.showErrors(errors);

                    btn.button('reset');
                }
            });
        },
        showErrors: function (errors) {
            this.hideErrors();

            _.each(errors, function (error) {
                let formElement = document.getElementById(`${this.$uniqId}_${error.property_path}`)
                if(!formElement){
                    this.createMessageBox('danger', 'Oh no!', error);
                    return;
                }
                let controlGroup = formElement.parentElement;
                controlGroup.classList.add('has-error')
                let helpBlock = controlGroup.querySelector('.help-block');
                  if(null === helpBlock){
                      helpBlock = document.createElement('div')
                      helpBlock.classList.add('help-block')
                      helpBlock.innerHTML = error.message
                      controlGroup.append(helpBlock)
                    return;
                  }
                helpBlock.innerHTML = error.message;
            }, this);
        },
        hideErrors: function () {

            let controlGroups = document.querySelectorAll(`.form-control`);
            controlGroups.forEach((el) => {
                el.parentElement.classList.remove('has-error')
            })
            let helpBlocks = document.querySelectorAll(`.help-block`);
            helpBlocks.forEach((el) => {
                el.innerHTML = ''
            })
        },
        isModelInvalid: function () {
            return this.model.validate(this.getModelViewAttr());
        },
        getModelViewAttr: function () {

            let data = $('form').serializeJSON();
            data[this.$uniqId].infoText = CKEDITOR.instances[`${this.$uniqId}_infoText`].getData()
            data[this.$uniqId].thankYouText = CKEDITOR.instances[`${this.$uniqId}_thankYouText`].getData()

            data.collection = this.collection
            data.name = data[this.$uniqId].name
            data.email = data[this.$uniqId].email
            data.action = data[this.$uniqId].action
            data.uniqid = this.$uniqId
            return data
        },
        createMessageBox: function (level, title, message) {
            document.getElementById('messageBox').innerHTML = _.template(__message, {
                level: level,
                title: title,
                message: message
            });
        }
    })
});
