
  
//RENDER UI angepasst ENDE
function renderUI(obj) {		
	obj.id = obj.attr('id');

	obj.html(
		'<div class="plupload_wrapper">' +
			'<div class="ui-widget-content plupload_container">' +
				'<div class="ui-state-default ui-widget-header plupload_header">' +
					'<div class="plupload_header_content">' +
					'<div class="plupload_buttons"><!-- Visible -->' +
							'<a class="plupload_button plupload_add">' + _('Add Files') + '</a>&nbsp;' +
							'<a class="plupload_button plupload_start">' + _('Start Upload') + '</a>&nbsp;' +
							'<a class="plupload_button plupload_stop plupload_hidden">'+_('Stop Upload') + '</a>&nbsp;' +
						'</div>' +
						'<div class="plupload_view_switch">' +
							'<input type="radio" id="'+obj.id+'_view_list" name="view_mode_'+obj.id+'" checked="checked" /><label class="plupload_button" for="'+obj.id+'_view_list" data-view="list">' + _('List') + '</label>' +
							'<input type="radio" id="'+obj.id+'_view_thumbs" name="view_mode_'+obj.id+'" /><label class="plupload_button"  for="'+obj.id+'_view_thumbs" data-view="thumbs">' + _('Thumbnails') + '</label>' +
						'</div>' +
					'</div>' +
				'</div>' +

				'<table class="plupload_filelist plupload_filelist_header ui-widget-header">' +
				'<tr>' +
					'<td class="plupload_cell plupload_file_name">' + _('Filename') + '</td>' +
					'<td class="plupload_cell plupload_file_status">' + _('Status') + '</td>' +
					'<td class="plupload_cell plupload_file_size">' + _('Size') + '</td>' +
					'<td class="plupload_cell plupload_file_action">&nbsp;</td>' +
				'</tr>' +
				'</table>' +

				'<div class="plupload_content">' +
					'<div class="plupload_droptext">' + _("Drag files here.") + '</div>' +
					'<ul class="plupload_filelist_content"> </ul>' +
					'<div class="plupload_clearer">&nbsp;</div>' +
				'</div>' +
					
				'<table class="plupload_filelist plupload_filelist_footer ui-widget-header">' +
				'<tr>' +
					'<td class="plupload_cell plupload_file_name">' +
						

						'<div class="plupload_started plupload_hidden"><!-- Hidden -->' +
							'<div class="plupload_progress plupload_right">' +
								'<div class="plupload_progress_container"></div>' +
							'</div>' +

							'<div class="plupload_cell plupload_upload_status"></div>' +

							'<div class="plupload_clearer">&nbsp;</div>' +
						'</div>' +
					'</td>' +
					'<td class="plupload_file_status"><span class="plupload_total_status">0%</span></td>' +
					'<td class="plupload_file_size"><span class="plupload_total_file_size">0 kb</span></td>' +
					'<td class="plupload_file_action"></td>' +
				'</tr>' +
				'</table>' +

			'</div>' +
			'<input class="plupload_count" value="0" type="hidden">' +
		'</div>'
	);
}
//RENDER UI angepasst ENDE
