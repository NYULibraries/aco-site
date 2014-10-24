YUI().use("node", "event", 'anim', function(Y) {

    "use strict";

    var body = Y.one("body");

    /**
     * add fx plugin to module body
    */
    var content = Y.one('.navbar-collapse').plug(Y.Plugin.NodeFX, {
        from: { height: function( node ) { 
              return node.get('scrollHeight');}},
        to: {
            height: 0 
        },
        easing: Y.Easing.easeBoth,
        on: {
            end: function () {
                var responsiveToggleButton = Y.one('.navbar-toggle');
                responsiveToggleButton.toggleClass('collapsed');
            }
        },
        duration: .3
    });    

    function onResponsiveClick( event ) {
        event.preventDefault();
        content.fx.set('reverse', !content.fx.get('reverse')); 
        content.fx.run();
    }
    
    Y.one('body').delegate('click', onResponsiveClick, '.navbar-toggle');
    
});