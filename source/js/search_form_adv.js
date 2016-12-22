YUI().use("node", 'anim', "event", function(Y) {

    "use strict";

    var body = Y.one("body");


    var contentFormClose = Y.one('.filter-content').plug(Y.Plugin.NodeFX, {
        from: {
            height: function(node) {
                return node.get('scrollHeight');
            }
        },
        to: { height: 0 },
        easing: Y.Easing.easeBoth,
        on: {
            start: function() {
                var filterlink = Y.all('.addafilter-link');
                filterlink.removeClass('addafilter-link-available');


            },
            end: function() {
                var filterlink = Y.all('.addafilter-link');
                //  filterlink.toggleClass('open');
                filterlink.addClass('addafilter-link-available');
            }
        },
        duration: .5
    });


    var addLinkHide = Y.one('.addafilter-link').plug(Y.Plugin.NodeFX, {
        from: {
            height: function(node) {
                return node.get('scrollHeight');
            }
        },
        to: { height: 0 },
        easing: Y.Easing.easeBoth,
        on: {
            start: function() {
                var filterlink = Y.all('.addafilter-link');
                filterlink.toggleClass('open');
                // filterlink.removeClass('addafilter-link-available');

            },
            end: function() {
                // var filterlink = Y.all('.addafilter-link');
                //filterlink.toggleClass('open');
                //  filterlink.addClass('addafilter-link-available');
            }
        },
        duration: .5
    });


    function onSubmit(event) {

        event.preventDefault();

        var currentTarget = event.currentTarget,
            input1 = currentTarget.one('.group1 [type="text"]'),
            whichField1 = currentTarget.one('.group1 .field-select').get("value"),
            whichScope1 = currentTarget.one('.group1 .scope-select').get("value"),
            value1 = input1.get("value"),
            cleanedValue1 = value1.trim().replace(/\s/g, '+').toLowerCase();
        var destinationString = whichField1 + "=" + cleanedValue1;
        var input2 = currentTarget.one('.group2 [type="text"]'),
            whichField2 = currentTarget.one('.group2 .field-select').get("value"),
            whichScope2 = currentTarget.one('.group2 .scope-select').get("value"),
            value2 = input2.get("value"),
            cleanedValue2 = value2.trim().replace(/\s/g, '+').toLowerCase();
        if (cleanedValue2) {
            destinationString += "&" + whichField2 + "=" + cleanedValue2;
        }
        Y.log("Zounds! DestinationString is " + destinationString);
        location.href = currentTarget.get("action") + "?" + destinationString;


    }

    function onAddFilterClick(event) {
        Y.log("onAddFilterClick");
        event.preventDefault();
        //  var formsetToClone = Y.one('.group1 fieldset');
        //   addLinkHide.fx.set('reverse', false);
        //addLinkHide.fx.run();
        var filterlink = Y.all('.addafilter-link');
        filterlink.toggleClass('open');
        contentFormClose.fx.set('reverse', true);
        contentFormClose.fx.run();


    }

    function onRemoveFilterClick(event) {
        event.preventDefault();
        //var formsetToClone = Y.one('.group1 fieldset');
        var filterlink = Y.all('.addafilter-link');
        filterlink.removeClass('open');
        contentFormClose.fx.set('reverse', false);
        contentFormClose.fx.run();
        // addLinkHide.fx.set('reverse', true);
 // addLinkHide.fx.run();
    }
    body.delegate('click', onAddFilterClick, '.addafilter-link-available');
    body.delegate('click', onRemoveFilterClick, '.remove-filter');
    body.delegate("submit", onSubmit, "form.advanced");

});