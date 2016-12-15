YUI().use("node", "event", function(Y) {

    "use strict";

    var body = Y.one("body");

    function onSubmit(event) {

        event.preventDefault();

        var currentTarget = event.currentTarget,
            input1 = currentTarget.one('.group1 [type="text"]'),
            whichField1 = currentTarget.one('.group1 .field-select').get("value"),
            whichScope1 = currentTarget.one('.group1 .scope-select').get("value"),
            value1 = input1.get("value"),
            cleanedValue1 = value1.trim().replace(/\s/g, '+').toLowerCase(),
            input2 = currentTarget.one('.group2 [type="text"]'),
            whichField2 = currentTarget.one('.group2 .field-select').get("value"),
            whichScope2 = currentTarget.one('.group2 .scope-select').get("value"),
            value2 = input2.get("value"),
            cleanedValue2 = value2.trim().replace(/\s/g, '+').toLowerCase(),
            destinationString = whichField1 + "=" + cleanedValue1;
        destinationString += "&" + whichField2 + "=" + cleanedValue2;

        Y.log("Zounds! a nascent advanced search destinationString is " + destinationString);
        location.href = currentTarget.get("action") + "?" + destinationString;


    }

    body.delegate("submit", onSubmit, "form.advanced");

});