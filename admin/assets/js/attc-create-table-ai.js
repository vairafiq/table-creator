jQuery(document).ready(function($){

// generate table data
$( '#attc_ai_table_create_form' ).on('submit', function (e) {
        e.preventDefault();
        var $this = $(this);
        var $prompt = $this.find('#attc_ai_prompt').val();

        // return if not found prompt
        if ( ! $prompt ) {
            alert('No instruction found!');
            return;
        }

        const form_data = new FormData();
        // ajax action
        form_data.append( 'action', 'tablegen_ai_table' );
        form_data.append( 'prompt', $prompt );
    
        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            // async: false,
            url: attc_aiobj.ajax_url,
            data: form_data,
            beforeSend() {
                // show loader in button
                $('#attc_ai_table_create_submit').empty().append('<span class="loader"></span>');
            },
            success( response ) {
                // hide loader and show buttons
                $('#attc_ai_table_create_submit').empty().append('Create with AI');
                $('.attc_response_actions').show();

                if ( response.error ) {
                    console.log({ response });

                    return;
                }

                // append table data
                $('.attc_notice').html( response );

                console.log({ response });

            },

            error( response ) {
                // hide loader
                $('#attc_ai_table_create_submit').empty().append('Create with AI');
                console.log({ response });
            },
        });


    });



    // copy shortcode
    $('#attc_ai_copy_shortcode').on('click', function() {
        // Get the content to copy
        var attc_table_shortcode = '[attc id="id"]';
        
        // copy to clipboard
        navigator.clipboard.writeText(attc_table_shortcode).then(function() {
            // Success
            alert('Shortcode copied to clipboard!');
        }).catch(function(err) {
            // Error
            console.error('Failed to copy: ', err);
        });
    });

 

});