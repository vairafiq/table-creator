(function (j) {
    var $ = j; // assigning jquery to $ var again though j already means jQuery, just because phpstorm and jslint gives proper hinting if done this ya
    // Ensure the global `attc` object exists.
    window.attc = window.attc || {};

    // build necessary objects in global attc object
    attc.table = {
        $row_input_field : $('#table_row'),
        $col_input_field : $('#table_column'),
        current_rows_length : parseInt($("#attc_table_admin").find("tbody tr").length, 10),
        current_column_length : $('#attc_table_admin').find('tbody tr:last td').length - 2, // get number length except first two empty tds
        tr_cell_pre: "<tr><td class='first_td'><span class='index_num'></span></td><td class='first_td'><input type='checkbox' class='row_check'></td>'",
        td_cell: '<td><textarea placeholder="Edit" class="attc_textarea" rows="2" cols="20"></textarea></td>',
        tr_cell_post: '</tr>',

        reindex: function () {
            $('#attc_table_admin').find('tbody tr').each(function (rowIDx) {
                // rearrange the Serial number of the row in the first row
                $(this).find('span.index_num').html(rowIDx + 1);

                // reindex all the tds in the table except first 2 tds
                $(this).children('td').not('.first_td').each(function (tdx, td) {
                    $(td).find('textarea').attr('name', 'table_values[' + (rowIDx) + '][' + (tdx) + ']');
                });
            });

            // reindex the columns eg. A, B, C, D, E except first 2 THs
            $('#attc_table_admin').find('thead tr th').not('.first_th').each(function (idx) {
                $(this).html("<span class='letter'>"+ attc.helper.number_to_letter(idx + 1)+"</span><input type='checkbox' class='check'>" );
            });
        }

    };



    // column Object
    attc.columns = {
      delete : function () {
          var $checkedElem = $('.check:checked'); // get the checked box inputs object
          $checkedElem.each(function () {
              var $this = $(this);
              var curIndex = $this.parent('th').index(); // th's index
              $this.parent('th').remove(); // remove table header column

              $('#attc_table_admin').find('tbody tr').each(function (rowIDx) {
                  var $tds = $(this).children('td');
                  $tds.eq(curIndex).remove(); // remove table data column
              });
          });

          // update the value in the input fields
          attc.table.$col_input_field.attr('value', attc.table.current_column_length - $checkedElem.length);
          attc.table.current_column_length -= $checkedElem.length;


      },
      confirm_delete: function () {
          swal({
                  title: "Are you sure?",
                  text: "You will lose data in the selected column(s) if you delete it",
                  type: "warning",
                  showCancelButton: true,
                  confirmButtonColor: "#DD6B55",
                  confirmButtonText: "Yes, delete it!",
                  closeOnConfirm: false
              },
              function(){
                  attc.columns.delete();
                  attc.table.reindex();
                  swal({
                      title: "Deleted!!",
                      type:"success",
                      timer: 400,
                      showConfirmButton: false });

              });
        },
        create: function () {
            var $col = $('#col'); // cache the input field
            var colsTobeAdded = parseInt($col.val(), 10); // get the value
            $col.val(''); // empty out the field


            if (!isNaN(colsTobeAdded)) {
                attc.table.current_column_length = $('#attc_table_admin').find('tbody tr:last td').length - 2;
                var new_col_value = colsTobeAdded + parseInt(attc.table.$col_input_field.attr('value'), 10);
                var $table = $('#attc_table_admin');
                var $thead_row = $table.find('thead tr');
                var $tbody_row = $table.find('tbody tr');
                //run a loop IT MUST BE IMPROVED
                for (var i = 0; i < colsTobeAdded; i++) {
                    $thead_row.append("<th><span class='letter'>" + attc.helper.number_to_letter(attc.table.current_column_length + i + 1) + "<span><input type='checkbox' class='check'></th>");
                    $tbody_row.each(function (c_row_id /*, cur_row*/) {
                        $(this).append(attc.table.td_cell);
                    });
                }

                attc.table.reindex();
                // update the object row value
                attc.table.$col_input_field.attr('value', new_col_value); // update in the DOM
                attc.table.current_column_length = new_col_value; // update in the OBJECT
            } else {

                swal("Enter a number", "Enter how many columns you would like to add");
                // alert('Enter a number');
            }
        }
    };







    // rows object
    attc.rows = {
        delete: function () {
            var $checked_rows = $('.row_check:checked'); // added 'var' key to make it scoped
            $checked_rows.each(function (idx) {
                $(this).closest('tr').remove();
            });
            attc.table.$row_input_field.attr('value', parseInt(attc.table.$row_input_field.attr('value'), 10) - $checked_rows.length);// update the row input fields
        },
        confirm_delete: function () {
            swal({
                    title: "Are you sure?",
                    text: "You will lose data in the selected row(s) if you delete it",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    closeOnConfirm: false
                },
                function(){
                    attc.rows.delete();
                    attc.table.reindex();
                    swal({
                        title: "Deleted!!",
                        type:"success",
                        timer: 400,
                        showConfirmButton: false })

                });
        },
        create: function () {
            var $row = $('#row'); // cache the field
            var rowTobeAdded = parseInt($row.val(), 10); // get the value
            $row.val('');// empty out the field
            var curRowLen = $('#attc_table_admin').find('tbody tr:last td').length - 2;
            if (!isNaN(rowTobeAdded)) {
                var new_row_html = ''; // build the markup
                for (var i=0; i < rowTobeAdded; i++){
                    new_row_html += attc.table.tr_cell_pre;
                        for(var j=0; j < attc.table.current_column_length; j++){
                            new_row_html += attc.table.td_cell;
                        }
                    new_row_html += attc.table.tr_cell_post;
                }

                $('#attc_table_admin').find('tbody').append(new_row_html);// append new row markup to the dom
                attc.table.reindex(); // reindex
                attc.table.$row_input_field.attr('value', rowTobeAdded + parseInt(attc.table.$row_input_field.attr('value'), 10));// update the row input fields
            } else {
                swal("Enter a number", "Enter how many rows you would like to add");
            }
        }
    };



    // Helper object
    attc.helper = {
        ajax_handler: function (ElementToShowLoadingIconAfter, data_to_send, CallBackHandler) {

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data:data_to_send,
                beforeSend: function () {
                    jQuery("<span class='attc_lp_ajax_loading'></span>").insertAfter(ElementToShowLoadingIconAfter);
                },
                success: function (data) {

                    jQuery(".attc_lp_ajax_loading").remove();
                    CallBackHandler(data);
                }
            });
        },
        prepare_ajax_data: function (action, nonce_field_id) {
            // initialize the vars
            var $table_body = $('#attc_body'),
                table_options = {
                    current_rows: $('#table_row').val(),
                    current_cols: $('#table_column').val(),
                    table_name: $('#table_name').val(),
                    table_description: $('#table_description').val()
                },
                table_data = [];

                // get all the data from the textarea of the table body and put them in the array
                $table_body.children().each( function( idx, row ) {
                    table_data[ idx ] = $( row ).find( 'textarea' )
                        .map( function() {
                            return $(this).val();
                        } )
                        .get();
                } );
            //console.log(table_data);
            table_data = JSON.stringify( table_data );
           // console.log(table_data);

            // return the prepared data object (for being used to send as data in an ajax call)
            return {
                action:action,
                _wpnonce:$(nonce_field_id).val(),
                attc_table_data: {
                    table_id: parseInt($('#table_id').val(), 10),
                    table_data: table_data,
                    table_options: table_options
                }
            }
        },
        isJson: function (item) {
            item = typeof item !== "string"
                ? JSON.stringify(item)
                : item;

            try {
                item = JSON.parse(item);
            } catch (e) {
                return false;
            }

            return (typeof item === "object" && item !== null);
        },
        resetForm: function ($form) {
            $form.find('input:text, input:password, input:file, select, textarea').val('');
            $form.find('input:radio, input:checkbox')
                .removeAttr('checked').removeAttr('selected');
        },
        auto_cLose_message : function(msg, duration) {
            $('#attc-lp-notification').remove(); // remove previously added notification
            var el = document.createElement("div");
            el.setAttribute('id', 'attc-lp-notification');
            el.innerHTML = msg;
            setTimeout(function () {
                el.parentNode.removeChild(el);
            }, duration);
            document.body.appendChild(el);
        },
        number_to_letter: function (number) {
            var column = '';
            while (number > 0) {
                column = String.fromCharCode(65 + ( ( number - 1) % 26 )) + column;
                number = Math.floor((number - 1) / 26);
            }
            return column;
        },
        letter_to_number: function (column) {
            column = column.toUpperCase();
            var count = column.length,
                number = 0,
                i;
            for (i = 0; i < count; i++) {
                number += ( column.charCodeAt(count - 1 - i) - 64 ) * Math.pow(26, i);
            }
            return number;
        }

    };



    // prevent invalid export table from submitting
    $( 'form#attc_export_form').on( 'submit', function( /* event */ ) {
        var selected_tables = $( '#tables-export' ).val(),
            num_selected = ( selected_tables ) ? selected_tables.length : 0;


        // only submit form, if at least one table was selected
        if ( 0 === num_selected ) {
            return false;
        }

        // at this point, the form is valid and will be submitted

        // add selected tables as a list to a hidden field
        // $( '#tables-export-list' ).val( selected_tables.join( ',' ) );

        // on form submit: Enable disabled fields, so that they are transmitted in the POST request
        $( '#tables-export-zip-file' ).prop( 'disabled', false );
    } );

    $( '#tables-export-select-all' ).on('click', function () {
        $( '#tables-export' ).find('option').prop('selected', this.checked)
    });



    var zip_checked_manually = false;
    $( '#tables-export-zip-file' ).on( 'change', function() {
        zip_checked_manually = $(this).prop( 'checked' );
    } );

    $('#tables-export').on('change', function () {
        var selected_tables = $(this).val(),
            zip_required = ( selected_tables.length > 1 ); // true or false
        // disable the checkbox if the zip is required and mark the input as checked
        $( '#tables-export-zip-file' )
            .prop( 'disabled', zip_required )
            .prop( 'checked', zip_required || zip_checked_manually );
        $( '#tables-export-zip-file-description' ).toggle( zip_required );
        if (selected_tables.length > 1){
            $('#tables-export-zip-file').prop('checked', true);
        }


        // set state of "Select all" checkbox, if no option is left unselected.
        $( '#tables-export-select-all' ).prop( 'checked', 0 === $(this).find( 'option' ).not( ':selected' ).length );
    });




    /*Show select box for table to replace only if needed*/
    $( '#row-import-type' ).on( 'change', 'input', function() {
        // make the select table list disable if the import type input does not have the value replace or append when any radio button is changed
        $( '#tables-import-existing-table' ).prop( 'disabled', ( 'replace' !== $(this).val() && 'append' !== $(this).val() ) );
    } );

    /**
     * Show only the import source field that was selected with the radio button
     *
     * @since 1.0.0
     */
    $( '#row-import-source' ).on( 'change', 'input', function() {
        $( '#row-import-source-file-upload, #row-import-source-url, #row-import-source-server, #row-import-source-form-field' ).hide();
        $( '#row-import-source-' + $(this).val() ).show();
    } );










    /*
     * Add column or row SECTION
     *
     * RESPOND TO THE EVENTS
     * */

    // append row to the table
    $('#irow').click(function (e) { e.preventDefault(); attc.rows.create(); });

    // append column to the table
    $('#icol').click(function (e) { e.preventDefault(); attc.columns.create(); });


    // Delete selected column(s)
    $('#del_col').on('click', function () { attc.columns.confirm_delete(); });

    // Delete selected rows
    $('#del_row').on('click', function () { attc.rows.confirm_delete(); });









    // home tab's tab
    $(".btn-pref .btn").click(function () {
        $(".btn-pref .btn").removeClass("btn-primary").addClass("btn-default");
        // $(".tab").addClass("active"); // instead of this do the below
        $(this).removeClass("btn-default").addClass("btn-primary");
    });

    // notification close button functionality
    $(document).on('click', '#attc_close_it', function (e) {
        $(this).closest('div').hide();
    });



















    /*
     * Table setting form page
     *
     */
    $('#attc-table-setting').on('click', function (e) {
        e.preventDefault();
        var form = $("#attc_setting_form");
        // prepare the form data to send to the server via ajax and add proper ajax action name and _nonce
        var formData = form.serialize()+'&action=attc_setting_handler&id='+form.data('id')+'&_wpnonce='+$('#_wpnonce').val();
        $("#successResult").remove();
        var $spiner_location = $(this);


        attc.helper.ajax_handler($spiner_location, formData, function (data) {
            //console.log(data);
            //$('<pre>'+data+'</pre>').insertAfter($spiner_location); // for testing purpose
            if (data === 'success') {
                swal({
                    title: "Success",
                    text: "Data Saved",
                    type: "success",
                    timer: 1000,
                    showConfirmButton: false
                });
            } else {
                swal({
                    title: "ERROR!",
                    text: "Something went wrong !!!",
                    type: "error",
                    timer: 2000,
                    showConfirmButton: false
                });
                // for debugging only: add this : <pre>'+data+'</pre> below
                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter($spiner_location);

            }
        });
    });

    // UPDATE THE TABLE AND
    // save settings on updating table data
    $('#attc-create-table').on('click', function (e) {
        // attc.helper.auto_cLose_message('Success: Data saved. <span id="attc_close_it">X</span>', 2000);
        e.preventDefault();
        var formData = attc.helper.prepare_ajax_data('update_tablegen_data', '#_wpnonce');
        $("#successResult").remove();
        var $spiner_location = $(this);

        attc.helper.ajax_handler($spiner_location, formData, function (data) {
            //console.log(data);
            //$('<pre>'+data+'</pre>').insertAfter($spiner_location); //uncomment it when debugging to see what data has been sent from the server.
            if (data === 'success') {
                // data saved successfully, now lets save the table's settings and that will show the success alert as that use the sweet alert library.
                $('#attc-table-setting').click();
            } else {
                swal({
                    title: "ERROR!",
                    text: "Something went wrong !!!",
                    type: "error",
                    timer: 2000,
                    showConfirmButton: false
                });
                // for debugging only: add this : <pre>'+data+'</pre> below

                $('<div class="notice notice-error is-dismissible" id="successResult"><p>Error: Something went wrong.<pre>' + data + '</pre></p></div>').insertAfter($spiner_location);

            }
        });
        // lets close it for a while for testing
        //@todo; uncomment the following lines after debugging
        //$('#attc-table-setting').click();
    });


    // Refresh the page on user click on refresh button
    $('#refreshPage').on('click', function (e) {
        e.preventDefault();
        location.reload();
    });



    // delete single table. next add this to the table object.
    $('.confirmation').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        swal({
                title: "Are you sure?",
                text: "You can not undo this",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function(){
                // $this.context.search.replace('?', ' '); // get the query string from the link and then remove this '?' eg.  page=table-generator-all&action=delete&table=12

            attc.helper.ajax_handler('', $this.context.search.replace('?', ' '), function (data) {

                if (data === 'success') {
                    $this.closest('tr').fadeOut();

                    swal({
                        title: "Deleted!!",
                        type:"success",
                        timer: 400,
                        showConfirmButton: false })
                } else {
                    swal({
                        title: "Error Deleting table!!",
                        type:"error",
                        timer: 1000,
                        showConfirmButton: false })

                }
            });





            });
    });


//    toggle radio button class on edit table screen
    $('.option_toggler a').on('click', function () {
        var $this = $(this);
        var sel = $this.data('title'); //  get data title of the link, eg. 'Y'.
        var tog = $this.data('toggle'); // get toggle data of the link, eg. acceptInputID
        var bla =  $('#' + tog).prop('value', sel);
        $this.addClass('active');
        $this.siblings('a').removeClass('active');
        // $('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
        // $('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
    });




//    hide general notification slowly
    var $notification = $('#attc_global_notification');
    if ($notification.length) {
        setTimeout(function () {
            $notification.fadeOut('slow');
        }, 4000);
    }

    // setting page js
    $('.setting_buttons .attc_btn').on('click',function(){
        $('#attc_table_setting').click();
    });

    // experimental
    $( '.inputfile' ).each( function()
	{
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'p' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
	});

    // Textarea Resizing function
    $(".attc-tbody").on('focusin', '.attc_textarea', function(){
        var $this = $(this),
        $curRowTextarea = $this.parent('td').siblings('td').children('.attc_textarea');
        $curRowTextarea.addClass('expand');
        $('.attc_textarea').not($curRowTextarea).removeClass('expand');
        $this.addClass('expand');
    });


    // window scroll event codes
    var boardInner = $('.board-inner');
    $(window).scroll(function(){
        var scrollTop = $(document).scrollTop();
        if(scrollTop > 128){
            boardInner.addClass('fix_top');
        }
        else{
            boardInner.removeClass('fix_top');
        }
    });
    $(".attc_option input[type='text']").on('focusin',function(){
        $(this).attr('placeholder','Enter a number');
    });
    $(".attc_option input[type='text']").on('focusout',function(){
        $('#row').attr('placeholder','Insert Rows');
        $('#col').attr('placeholder','Insert Columns');
    });
})(jQuery);
