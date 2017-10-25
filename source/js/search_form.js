YUI().use("node", "event", function(Y) {

    "use strict";
    console.log("*** Search for simple! I believe this file is no longer used in ACO 2017-06-07 LMH ***************");
    var body = Y.one("body");
    
    function onSubmit(event) {

        event.preventDefault();

        var currentTarget = event.currentTarget
          , input = currentTarget.one('[type="text"]')
          , value = input.get("value");
        
        if ( value.length ) {
            
            location.href = currentTarget.get("action") + "?q=" + value.trim().replace(/\s/g, '+').toLowerCase();
        }

    }
    
    body.delegate("submit", onSubmit, "form.simple");
 
});
