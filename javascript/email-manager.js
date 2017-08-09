/**
 * Wrapper function to safely use $
 */
function wpemWrapper( $ ) {
    var wpem = {

        /**
         * Main entry point
         */
        init: function () {
            wpem.prefix      = 'wpem_';
            wpem.templateURL = $( '#template-url' ).val();
            wpem.ajaxPostURL = $( '#ajax-post-url' ).val();
           
            wpem.registerEventHandlers();
            wpem.initUploader();
            $('.wpem-date').datepicker({
                dateFormat : 'yy-mm-dd'
            });
            $("#wpem-us-autocomplete").autocomplete({
                delay: 500,
                minLength: 2, 
                source: wpem.userSearch,  
                select: wpem.userSearchMail
            });
        },

        /**
         * Registers event handlers
         */
        registerEventHandlers: function () {
            $( '#wpem-insert-all' ).click( wpem.insertShortCode );
            $( '#wpem-shortcode' ).click( wpem.insertShortCode );
            $( '#wpem-start-form-val').click(wpem.hideForm);
            $( '#wpem-start-custom-val').click(wpem.hideForm);
            $( '#custom-notification').click(wpem.notificationForm);
            $( '#wpem-form-selector').change(wpem.selectForm);
            $( '.wpem-template-selector').change(wpem.loadTemp);
            $( '.wpem-data-source').click(wpem.changeDataSource);
            $( '#wpem-notification-template').change(wpem.loadNTemp);
            $('.wpem_add_repeatable').click(wpem.cloneRepeatable);
            $('.wpem_repeatable_table').delegate('.wpem_remove_repeatable','click',wpem.removeRepeatable);
            $('.wpem_add_css').click(wpem.liveCSS);
            $('#wpem_test_mail').click(wpem.testMail);
			
        },
		
        userSearch: function(request, response){
            query = {
                action: 'wpem_all_ajax',
                'wpem_ajx': 'user_search',
                'term':request.term,
                _wpnonce: $('#wpem_us_nonce').val()
            };
				 
            $.getJSON(ajaxurl, query,function(data){
                response(data);
            });
        },
		
        userSearchMail: function( event, ui ){
            $("#wpem-us-autocomplete-email").val(ui.item.email);
        },
		

        /**
         *
         * @param object event
         */
        insertShortCode: function ( event ) {
            var msg;
            if($(this).attr('id')=='wpem-insert-all'){
                msg ='[email_manager_all_fields]';
            }else{
                msg='[e_manager id='+$('#wpem-fields-select').val()+']';
            }
			
            if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
                ed.focus();
                if (tinymce.isIE)
                    ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
                ed.execCommand('mceInsertContent', false, msg);
            } else {
                if (typeof wpActiveEditor == 'undefined') wpActiveEditor = 'content';
                edInsertContent(edCanvas, msg);
            }
					
            event.preventDefault();
        },
		
        hideForm: function(event){
            if(this.id == "wpem-start-form-val"){
                $('#wpem-start-custom-select').hide();
                $('#wpem-start-form-select').show();
            }else if(this.id == "wpem-start-custom-val"){
                $('#wpem-start-custom-select').show();
                $('#wpem-start-form-select').hide();
            }
        },
		
        notificationForm: function(event){
		    
            if ($(this).attr('checked')) {
                $('.wpem-hide, .wpem-show').show();
            }else{
                $('.wpem-hide, .wpem-show').hide();
            }

        },
		
        selectForm: function(event){
            $(this).next('.spinner').show();
            wpem.reloadWithParameter('nf_id',this.value);
        },
		
        changeDataSource:function(event){
            $(this).next('.spinner').show();
            wpem.reloadWithParameter('data_source',this.value);
        },
		
        reloadWithParameter: function(param, value){
            var newLocation
            var  activUrl = location.href;
            var Reg=new RegExp(param+'=(\\w+)');
            var match  = activUrl.match(Reg);
            if(match){
                newLocation = activUrl.replace(Reg,param+'='+value);
            }else{
                newLocation = window.location+'&'+param+'='+value;
            }
            window.open(newLocation,'_parent');
        },
		
        liveCSS: function(event){
            event.preventDefault();
            var css = document.getElementById('wpem_css-box-field').value;
            jQuery( '#content_ifr' ).contents().find( '#mceDefaultStyles' ).html("");
            tinyMCE.activeEditor.dom.addStyle(css);
        },
		
        testMail: function(event){
            var body, temp_id, mail_format,to_email;
			
			$(this).siblings('.spinner').show();
			
            if ( typeof(tinymce) != 'undefined' && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden()) {
                body = tinyMCE.activeEditor.getContent();
            }else{
			    body=document.getElementById('wpem_email_body').value;
			}
			
            mail_format = document.getElementById('wpem_mail_format').value;
            temp_id = document.getElementById('wpem_template').value;
			
            var data = {
                action: 'wpem_all_ajax',
                wpem_ajx: 'test_mail',
                temp_id: temp_id,
                m_format: mail_format,
                m_body: body
            };
            $.getJSON(ajaxurl, data, function (msg) {
			    if(msg.error){
				     alert(msg.error);
				}else{
				     alert(msg.sent);
				}
				
                $('.spinner').hide();
                });		
            return false;
        },
		
        cloneRepeatable: function(event){
            event.preventDefault();
            var button = $( this ),
            row = button.parent().parent().prev( 'tr' ),
            clone = wpem.clone_repeatable(row);
            if(row.hasClass('wpem-hide')){
                row.removeClass('wpem-hide');     
            }else
                clone.insertAfter( row );
		
        },
		
        clone_repeatable : function(row) {

            clone = row.clone();

            /** manually update any select box values */
			
            var count  = row.parent().find( 'tr' ).length - 1;

            clone.removeClass( 'wpem_add_blank' );

            clone.find( 'td input' ).val( '' );
            clone.find( 'input' ).each(function() {
                var name 	= $( this ).attr( 'name' );

                name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

                $( this ).attr( 'name', name ).attr( 'id', name );
            });

            return clone;
        },
		
        removeRepeatable : function(event) {
		
            event.preventDefault();

            var row   = $(this).parent().parent( 'tr' ),
            count = row.parent().find( 'tr' ).length - 1,
            repeatable = 'tr.wpem_repeatable_row';


            if( count > 1 ) {
                $( 'input', row ).val( '' );
                row.fadeOut( 'fast' ).remove();
            } else {
                row.addClass('wpem-hide');
            }

            /* re-index after deleting */
            $(repeatable).each( function( rowIndex ) {
                $(this).find( 'input' ).each(function() {
                    var name = $( this ).attr( 'name' );
                    name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
                    $( this ).attr( 'name', name ).attr( 'id', name );
                });
            });
        },
		
        initUploader : function() {
            if( typeof wp === "undefined" || '1' !== wpem_vars.new_media_ui ){
                //Old Thickbox uploader
                if ( $( '.wpem_upload_attachment_button' ).length > 0 ) {
                    window.formfield = '';

                    $('body').on('click', '.wpem_upload_attachment_button', function(e) {
                        e.preventDefault();
                        window.formfield = $(this).parent().prev();
                        window.tbframe_interval = setInterval(function() {
                            jQuery('#TB_iframeContent').contents().find('.savesend .button').val(wpem_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
                        }, 2000);
                        if (wpem_vars.post_id != null ) {
                            var post_id = 'post_id=' + wpem_vars.post_id + '&';
                        }
                        tb_show(wpem_vars.add_new_download, 'media-upload.php?' + post_id +'TB_iframe=true');
                    });

                    window.wpem_send_to_editor = window.send_to_editor;
                    window.send_to_editor = function (html) {
                        if (window.formfield) {
                            imgurl = $('a', '<div>' + html + '</div>').attr('href');
                            window.formfield.val(imgurl);
                            window.clearInterval(window.tbframe_interval);
                            tb_remove();
                        } else {
                            window.wpem_send_to_editor(html);
                        }
                        window.send_to_editor = window.wpem_send_to_editor;
                        window.formfield = '';
                        window.imagefield = false;
                    };
                }
            } else {
                // WP 3.5+ uploader
                var file_frame;
                window.formfield = '';

                $('body').on('click', '.wpem_upload_attachment_button', function(e) {

                    e.preventDefault();

                    var button = $(this);

                    window.formfield = $(this).closest('.wpem_repeatable_upload_wrapper');

                    // If the media frame already exists, reopen it.
                    if ( file_frame ) {
                        //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                        file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media( {
                        frame: 'post',
                        state: 'insert',
                        title: button.data( 'uploader_title' ),
                        button: {
                            text: button.data( 'uploader_button_text' )
                        },
                        multiple: $( this ).data( 'multiple' ) == '0' ? false : true  // Set to true to allow multiple files to be selected
                    } );

                    file_frame.on( 'menu:render:default', function( view ) {
                        // Store our views in an object.
                        var views = {};

                        // Unset default menu items
                        view.unset( 'library-separator' );
                        view.unset( 'gallery' );
                        view.unset( 'featured-image' );
                        view.unset( 'embed' );

                        // Initialize the views in our view object.
                        view.set( views );
                    } );

                    // When an image is selected, run a callback.
                    file_frame.on( 'insert', function() {

                        var selection = file_frame.state().get('selection');
                        selection.each( function( attachment, index ) {
                            attachment = attachment.toJSON();
                            if ( 0 === index ) {
                                // place first attachment in field
                                window.formfield.find( '.wpem_repeatable_attachment_id_field' ).val( attachment.id );
                                window.formfield.find( '.wpem_repeatable_upload_field' ).val( attachment.url );
                                window.formfield.find( '.wpem_repeatable_name_field' ).val( attachment.title );
                            } else {
                                // Create a new row for all additional attachments
                                var row = window.formfield,
                                clone = EDD_Download_Configuration.clone_repeatable( row );

                                clone.find( '.wpem_repeatable_attachment_id_field' ).val( attachment.id );
                                clone.find( '.wpem_repeatable_upload_field' ).val( attachment.url );
                                if ( attachment.title.length > 0 ) {
                                    clone.find( '.wpem_repeatable_name_field' ).val( attachment.title );
                                } else {
                                    clone.find( '.wpem_repeatable_name_field' ).val( attachment.filename );
                                }
                                clone.insertAfter( row );
                            }
                        });
                    });

                    // Finally, open the modal
                    file_frame.open();
                });


                // WP 3.5+ uploader
                var file_frame;
                window.formfield = '';
            }

        },
        loadTemp: function(){ //load schedule or send mail template
            $(this).next('.spinner').show();
            var data = {
                action: 'wpem_all_ajax',
                'wpem_ajx': 'load_temp',
                't_id': this.value,
                'type': typenow
            };
            wpem.loadTemplate(data);
            return false;
        },
		
        loadNTemp: function(){ //load notification template
            $(this).next('.spinner').show();
            var data = {
                action: 'wpem_all_ajax',
                'wpem_ajx': 'load_notification_temp',
                't_id': this.value,
                'n_id': jQuery('#notification_id').val(),
                'type': typenow
            };
            wpem.loadTemplate(data);
            return false;
        },
	
        loadTemplate: function(data){

            if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
                var editor_type = 'rich';
            } else {
                var editor_type = 'html';
            }
			
            data.editor_type=editor_type;

            jQuery.ajax({
                url: ajaxurl,
                data: data,
                dataType: 'JSON',
                success: function (msg) {
                    if (msg.error) {
                        alert(msg.error);
                    } else {
                        try { // we may not have the content 
                            if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
                                ed.focus();
                                if (tinymce.isIE)
                                    ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
                                ed.execCommand('mceSetContent', false, msg.body);
                            } else {
                                if (typeof wpActiveEditor == 'undefined') wpActiveEditor = 'content';
                                canvas = document.getElementById(wpActiveEditor);
                                canvas.value = msg.body;
                                canvas.focus();
                            }
                            jQuery( '#content_ifr' ).contents().find( '#mceDefaultStyles' ).html("");
                            tinyMCE.activeEditor.dom.addStyle(msg.css);
                            jQuery('#title').focus().attr('value', msg.title);
                        } catch (err) {
                        ;
                        }

                    }
                    $('.spinner').hide();
                }
            });
            return false;
		
        }
		
    }; // end wpem

    $( document ).ready( wpem.init );

} // end wpemWrapper()

wpemWrapper( jQuery );