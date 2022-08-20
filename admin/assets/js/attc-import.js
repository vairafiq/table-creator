
window.attc = window.attc || {};

(function ($) {
    
    $('body').on('submit', '#tablegen_imort_from_google', function(e){

        e.preventDefault();
        
        $('.attc_google_import_btn').html( 'Loading..' );
        $('.attc_google_import_btn').prop( 'disabled', true );

        var form = document.getElementById("tablegen_imort_from_google");

        let form_data = new FormData( form );
        form_data.append( 'action', 'tablegen_imort_from_google' );
        form_data.append( 'tablegen_nonce', attc_import_data.tablegen_nonce );
        form_data.append( 'sheet_key', $( 'input[name="sheet_key"]' ).val() );
        form_data.append( 'sheet_id', $( 'input[name="sheet_id"]' ).val() );
        form_data.append( 'table_name', $( 'input[name="table_name"]' ).val() );
        form_data.append( 'table_description', $( 'input[name="table_description"]' ).val() );

        $.ajax({
            method: 'POST',
            processData: false,
            contentType: false,
            url: attc_import_data.ajax_url,
            data: form_data,
            success: function (response) {
                $('.upload_wrapper').after( '<span class="attc_import_data_notice">' + response.msg + '</span>' );
                $("#tablegen_imort_from_google").trigger("reset");
            },
            error: function (error) {
                console.log(error);
            },
            complete: function () {
                setTimeout(function(){
                    $( '.attc_import_data_notice' ).remove();
                }, 2000);
                $('.attc_google_import_btn').html( 'Import Sheet' );
                $('.attc_google_import_btn').prop( 'disabled', false );
            }
        });
		
	});
})(jQuery);
