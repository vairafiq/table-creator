jQuery(document).ready(function($){
// table raw code
var attc_table_code;

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
                // store table code
                attc_table_code = response;
                
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



    // copy table sourcecode
    $('#attc_ai_copy_sourcecode').on('click', function() {
        
        // copy to clipboard
        navigator.clipboard.writeText(attc_table_code).then(function() {
            // Success
            alert('Sourcecode copied to clipboard!');
        }).catch(function(err) {
            // Error
            console.error('Failed to copy: ', err);
        });
    });




    // ceate new page with table data
    $('#attc_ai_use_in_page').on('click', function(e) {
        e.preventDefault();
        console.log('clicked');
        // title and content
        var pageTitle = "Page Title";
        var pageContent = attc_table_code;

        $.ajax({
            url: attc_aiobj.ajax_url,
            method: 'POST',
            data: {
                action: 'create_new_page_with_data',
                title: pageTitle,
                content: pageContent
            },
            beforeSend: function() {
                $('#attc_ai_use_in_page').html('Redirecting to new page...');
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;  // Redirect to the newly created page's edit screen
                } else {
                    $('#attc_ai_use_in_page').html('Use in New Page');
                    alert('Failed to create page: ' + response.data.message);
                }
            }
        });
    });
    

 

});