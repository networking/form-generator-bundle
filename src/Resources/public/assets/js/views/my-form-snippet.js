
define([
    "jquery", "underscore", "backbone","ckeditor",
    "views/snippet", "views/temp-snippet",
    "helper/pubsub"
], function(
    $, _, Backbone,CKEDITOR,
    SnippetView, TempSnippetView,
    PubSub
    ){
    return SnippetView.extend({
        events:{
            "click"   : "preventPropagation" //stops checkbox / radio reacting.
            , "mousedown" : "mouseDownHandler"
            , "mouseup"   : "mouseUpHandler"
        }

        , mouseDownHandler : function(mouseDownEvent){
            mouseDownEvent.stopPropagation();
            mouseDownEvent.preventDefault();
            var that = this;
            //popover
            $(".popover").remove();
            this.$el.popover({ placement: 'left' }).popover("show");
            $(".popover #save").on("click", this.saveHandler(that));
            $(".popover #cancel").on("click", this.cancelHandler(that));
            //add drag event for all but form name
            $("body").on("mousemove", function(mouseMoveEvent){
                if(
                    Math.abs(mouseDownEvent.pageX - mouseMoveEvent.pageX) > 10 ||
                    Math.abs(mouseDownEvent.pageY - mouseMoveEvent.pageY) > 10
                ){
                    that.$el.popover('destroy');
                    PubSub.trigger("mySnippetDrag", mouseDownEvent, that.model);
                    that.mouseUpHandler();
                };
            });
        }

        , preventPropagation: function(e) {
            e.stopPropagation();
            e.preventDefault();

            $.each(this.model.get('fields'), function (i, element) {
                if (element.type === 'ckeditor') {
                    var fieldName = 'ckeditor_'+element.name
                    CKEDITOR.replace(fieldName, {
                        "toolbar": [["Bold", "Italic", "Underline", "-", "NumberedList", "BulletedList","-",'TextColor', 'BGColor','Styles', 'Format', 'Font', 'FontSize']],
                        "language": "de","width": '100%'
                    });


                }
            })
        }

        , mouseUpHandler : function(mouseUpEvent) {
            $("body").off("mousemove");
        }

        , saveHandler : function(boundContext) {
            return function(mouseEvent) {
                mouseEvent.preventDefault();
                var fields = $(".popover .field");

                for(var instance in CKEDITOR.instances){
                    var ckeditorInstance = CKEDITOR.instances[instance];
                    var data = ckeditorInstance.getData();
                    $('#' + ckeditorInstance.name).val($.trim(data.replace(/[\t\n]+/g, ' ')));
                    CKEDITOR.instances[instance].destroy();
                }
                _.each(fields, function(e){
                    var $e = $(e)
                        , type = $e.attr("data-type")
                        , name = $e.attr("id");
                    switch(type) {
                        case "checkbox":
                            boundContext.model.setField(name, $e.is(":checked"));
                            break;
                        case "input":
                            boundContext.model.setField(name, $e.val());
                            break;
                        case "textarea":
                            boundContext.model.setField(name, $e.val());
                            break;
                        case "ckeditor":
                            name = name.replace('ckeditor_','');
                            boundContext.model.setField(name, $e.val());
                            break;
                        case "textarea-split":
                            boundContext.model.setField(name,
                                _.chain($e.val().split("\n"))
                                    .map(function(t){return $.trim(t)})
                                    .filter(function(t){return t.length > 0})
                                    .value()
                            );
                            break;
                        case "select":
                            var valarr = _.map($e.find("option"), function(e){
                                return {value: e.value, selected: e.selected, label:$(e).text()};
                            });
                            boundContext.model.setField(name, valarr);
                            break;
                    }
                });
                boundContext.model.trigger("change");
                $(".popover").remove();
            }
        }

        , cancelHandler : function(boundContext) {
            return function(mouseEvent) {
                mouseEvent.preventDefault();
                $(".popover").remove();
                boundContext.model.trigger("change");

                for(var instance in CKEDITOR.instances){
                    CKEDITOR.instances[instance].destroy();
                }
            }
        }

    });
});
