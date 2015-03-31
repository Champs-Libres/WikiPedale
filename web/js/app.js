/* jslint vars: true */
/*jslint indent: 3 */
/* global require */
'use strict';

require.config({
   paths: {
      'jQuery': 'bower_components/jquery/jquery',
      'ol': 'bower_components/ol3-unofficial/ol-debug',
      'select2': 'bower_components/select2/select2',
      'colorbox': 'bower_components/colorbox/jquery.colorbox',
   },
   shim: {
      'jQuery': {
         exports: '$'
      },
      'ol': {
         exports: 'ol'
      },
      'select2': {
         deps: ['jQuery'],
         exports: 'jQuery'
      },
      'colorbox':{
         deps: ['jQuery'],
         exports: 'jQuery'
      }
   }
});

require(
   [
      'jQuery', 'data_map_glue', 'informer', 'markers_filtering',
      'select2', 'colorbox', 'report_create', 'login', 'report_display', 'report_edit',
      'category','comments'
   ],
   function($, data_map_glue, informer, markers_filtering,
      select2, colorbox, report_create, login, report_display, report_edit,
      category, comments)
   {
      $.ajaxSetup({ cache: false }); // IE save json data in a cache, this line avoids this behavior
      $(document).ready(function(){
         var data_for_init = $('#data_for_init');
         var map_center_lon, map_center_lat, map_zoom_level;
         var selected_report_id = null;

         $('a.connexion').colorbox({
            inline:true,
            width:'400px',
            height:'400px',
            onComplete: function(){ $('#login_input_username').focus(); }
         });

         //Login
         $('#loginForm').submit(function(e) { e.preventDefault(); login.catch_form(); });

         // Add comment API KEY
         $('#add_comment_with_api_key__submit_button').click(function(e) { e.preventDefault(); comments.addWithApiKey(); });
         

         if (data_for_init.length !== 0) {
            map_center_lon = data_for_init.attr('data-map-center-lon');
            map_center_lat = data_for_init.attr('data-map-center-lat');
            selected_report_id = data_for_init.attr('data-selected-report-id');

            if(selected_report_id) {
               map_zoom_level = 17;
            } else  {
               map_zoom_level = 13;
            }
            
         } else {
            map_center_lon = 4.648801835937508;
            map_center_lat = 50.20168148245898;
            map_zoom_level = 8;
         }

         data_map_glue.initApp(map_center_lon, map_center_lat, map_zoom_level, selected_report_id);

         // Manager Filtering
         category.insertParentCategoryToSelectField('#optionsAffichageFilterCategoriesParent', ['short','medium']);

         //Category
         category.init(function() {markers_filtering.displayMarkersRegardingToFiltering();});

         //Category Filtering
         markers_filtering.initFormFor('category');
         markers_filtering.initFormFor('long_term_category');

         $('#span_report_description_cat_edit').select2();
         $('#span_report_description_status_edit').select2();
         $('#span_report_description_gestionnaire_edit').select2();
         $('#span_report_description_moderator_edit').select2();

         $('#add_new_report_form__category').select2().on('change', function() { informer.update_new_description_form('category'); });
         $('#add_new_report_form__draw_type').select2({'minimumResultsForSearch': -1}).on('change', function() { data_map_glue.changeDrawModeOnMap('new_report'); });
         $('#edit_report__draw_selection_option').select2({'minimumResultsForSearch': -1}).on('change', function() { data_map_glue.changeDrawModeOnMap('edit_report'); });

         $('#add_new_report_form__draw_details_on_map').click(function(e) { e.preventDefault(); data_map_glue.startDrawingDetailsOnMap('new_report'); });
         $('#add_new_report_form__end_draw_details_on_map').click(function(e) { e.preventDefault(); data_map_glue.endDrawingDetailsOnMap('new_report'); });
         $('#add_new_report_form__draw_erase').click(function(e) { e.preventDefault(); data_map_glue.eraseDrawingDetailsOnMap('new_report'); });
         $('#div_add_new_description__draw').hide();

         $('#edit_report__draw_button').click(function(e) { e.preventDefault(); data_map_glue.startDrawingDetailsOnMap('edit_report'); });
         $('#edit_report__end_draw_button').click(function(e) { e.preventDefault(); data_map_glue.endDrawingDetailsOnMap('edit_report'); });
         $('#edit_report__draw_erase_button').click(function(e) { e.preventDefault(); data_map_glue.eraseDrawingDetailsOnMap('edit_report'); });
         $('#edit_report__save_drawings_button').click(function(e) { e.preventDefault(); report_edit.saveDrawings(); });
         //$('#div_add_new_description__draw').hide();

         $('#div_returnNormalMode').hide();

         //ModeratorStatus Filtering 
         markers_filtering.initFormFor('moderator_status');
         markers_filtering.initFormFor('moderator_rejected_status');

         //Timestamp Filtering
         markers_filtering.initFormFor('timestamp');
         
         // Menu
         $('#div_add_new_description_button').click(function() { data_map_glue.modeChange(); });
         $('#div_add_new_description_cancel_button').click(function() { data_map_glue.modeChange(); });
         $('#div_returnNormalMode').click(function() { report_display.unactivateCommentMode(); });
         $('#filter_and_export_button').click(function() { markers_filtering.activateUnactivateFilteringForm(); } );
         $('#stop_filter_and_export_button').click(function() { markers_filtering.activateUnactivateFilteringForm(); } );

         // Add New Description
         $('#add_new_report_form__user_label').blur(function() { informer.update_new_description_form('user_label'); });
         $('#add_new_report_form__email').blur(function() { informer.update_new_description_form('email'); });
         $('#add_new_report_form__user_phonenumber').blur(function() { informer.update_new_description_form('user_phonenumber'); });
         $('#add_new_report_form__lieu').blur(function() { informer.update_new_description_form('lieu'); });
         $('#add_new_report_form__description').blur(function() { informer.update_new_description_form('description'); });
         $('#form__add_new_description').submit(function(e) { e.preventDefault(); report_create.catchCreatingForm(this); });
         $('#new_place_form_reset_button').click(function(e) { e.preventDefault(); report_create.clearCreatingForm(); });
         $('#add_new_report_form_informer__category_medium_warning').hide();

         //Place Description Edit
         $('#span_report_description_loc_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('loc'); });
         $('#span_report_description_desc_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('desc'); });
         $('#span_report_description_commentaireCeM_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('commentaireCeM'); });
         $('#span_report_description_cat_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('cat'); });
         $('#span_report_description_status_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('status'); });
         $('#span_report_description_gestionnaire_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('gestionnaire'); });
         $('#span_report_description_moderator_button').click(function(e) { e.preventDefault();  report_edit.edit_or_save('moderator'); });
         $('#span_report_description_delete_button').click(function(e) {e.preventDefault(); data_map_glue.lastDescriptionSelectedDelete(); });
         
         $('#button_edit_lon_lat').click(function(e) { e.preventDefault(); report_edit.position_edit_or_save(); });
         $('#button_save_lon_lat').click(function(e) { e.preventDefault(); report_edit.position_edit_or_save(); });
      
         markers_filtering.displayMarkersRegardingToFiltering();
      });
   });