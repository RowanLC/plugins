(function($) {
    function detectCopyEvent() {
        document.addEventListener('copy', function(event) {
            var selection = document.getSelection();
            var selectedText = selection.toString();
            var pageUrl = window.location.href;

            if (selectedText === pageUrl) {
                jQuery.post(
                    MyPluginAjax.ajaxurl,
                    {
                        action: 'wp_plugin_log_copy_event',
                        url: pageUrl
                    },
                    function(res) {
                        console.log('Copy event logged : ', res);
                    }
                );
            }
        });
    }
    
    function detectButtonClick() {
        $('#my-special-button').on('click', function() {
            $.post(
                MyPluginAjax.ajaxurl,
                {
                    action: 'plugin_log_button_click',
                    button_id: 'my-special-button'
                },
                function(res) {
                    console.log('Button click logged : ', res);
                }
            );
        });
    }

    $(document).ready(function() {
        detectCopyEvent();
        detectButtonClick();
    });
})(jQuery);