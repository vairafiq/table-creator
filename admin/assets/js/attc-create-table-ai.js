jQuery(document).ready(function($){


$( '#attc_ai_table_create_form' ).on('submit', function (e) {
        e.preventDefault();
        var $this = $(this);
        var $prompt = $this.find('#attc_ai_prompt').val();

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
                $('#attc_ai_table_create_submit').empty().append('<span class="loader"></span>');
            },
            success( response ) {

                $('#attc_ai_table_create_submit').empty().append('Create with AI');
                $('#attc_ai_copy_shortcode').show();
                $('#attc_ai_use_in_page').show();

                if ( response.error ) {
                    console.log({ response });

                    return;
                }

                $('.attc_notice').html( response );

                console.log({ response });

            },

            error( response ) {
                $('#attc_ai_table_create_submit').empty().append('Create with AI');
                console.log({ response });
            },
        });


    });

 

});