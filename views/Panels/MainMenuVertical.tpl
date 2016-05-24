<link rel="stylesheet" href="%%GLOBAL_AppPath%%/views/Styles/dropdown-menu-vertical.css" />
<script type="text/javascript" src="%%GLOBAL_AppPath%%/javascript/dropdown-menu.min.js"></script>
<div class="MainMenu MainMenuVertical">
	%%GLOBAL_MainMenuVertical%%
</div>
<script type="text/javascript">
$(function() {
    $('.MainMenuVerticalMenu').dropdown_menu({
        sub_indicator_class  : 'dropdown-menu-sub-indicator',   // Class given to LI's with submenus
        vertical_class       : 'dropdown-menu-vertical',        // Class for a vertical menu
        shadow_class         : 'dropdown-menu-shadow',          // Class for drop shadow on submenus
        hover_class          : 'dropdown-menu-hover',           // Class applied to hovered LI's
        open_delay           : 150,                             // Delay on menu open
        close_delay          : 300,                             // Delay on menu close
        animation_open       : { opacity : 'show' },            // Animation for menu open
        speed_open           : 'fast',                          // Animation speed for menu open
        animation_close      : { opacity : 'hide' },            // Animation for menu close
        speed_close          : 'fast',                          // Animation speed for menu close
        sub_indicators       : false,                           // Whether to show arrows for submenus
        drop_shadows         : false,                           // Whether to apply drop shadow class to submenus
        vertical             : true,                           // Whether the root menu is vertically aligned
        viewport_overflow    : 'auto',                          // Handle submenu opening offscreen: "auto", "move", "scroll", or false
        init                 : function() {}                    // Callback function applied on init
    });
});
</script>