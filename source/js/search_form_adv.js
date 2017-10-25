YUI().use("node", 'anim', "event", function(Y)
{

    "use strict";

    var body = Y.one("body");

    function onSubmit(event)
    {
        event.preventDefault();

        var currentTarget = event.currentTarget,
            whichField1 = currentTarget.one('.group1 .field-select').get("value"),
            whichScope1 = currentTarget.one('.group1 .scope-select').get("value"),
            input1 = currentTarget.one('.group1 [type="text"]'),
            value1 = input1.get("value"),
            destinationString;
        value1 = value1.trim();
       // Y.log("scope is " + whichScope1);
       // Y.log("value1 " + value1);
        if (value1 !== "")
        {
            destinationString = whichField1 + '=' + value1 + '&scope=' + whichScope1;
           // Y.log("Zounds! DestinationString is " + destinationString);
            location.href = currentTarget.get("action") + "?" + destinationString;
        }
        else
        {
            location.href = "/aco/searchcollections/";
        }

    }

    body.delegate("submit", onSubmit, "form.advanced");

});
