YUI().use("node", 'anim', "event", function(Y) {

    "use strict";

    var body = Y.one("body");

    function onSubmit(event) {

        event.preventDefault();

        var currentTarget = event.currentTarget,
            whichField1 = currentTarget.one('.group1 .field-select').get("value"),
            whichScope1 = currentTarget.one('.group1 .scope-select').get("value"),
            input1 = currentTarget.one('.group1 [type="text"]'),
            value1 = input1.get("value");
        value1 = value1.trim().replace(/\s/g, '+');
        Y.log("whichScope1 " + whichScope1);
        Y.log("value1 " + value1);
        if (whichScope1 == "contains" && (whichField1 != 'q')) {
            value1 = "*" + value1 + "*";
        }
        if (whichScope1 == "equals") {
            value1 = '"' + value1 + '"';
        }
        var destinationString = whichField1 + "=" + value1;

        Y.log("Zounds! DestinationString is " + destinationString);
        Y.log("Zounds! value1 is " + value1);
        if (value1 !== "") {
            location.href = currentTarget.get("action") + "?" + destinationString;
        } else {
            location.href = "/aco/searchcollections/";
        }

    }

    body.delegate("submit", onSubmit, "form.advanced");

});