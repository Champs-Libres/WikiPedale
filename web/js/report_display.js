/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

define(['jQuery','report_map','user','report','photo','params','report_edit','comments','basic_data_and_functions'],
      function($,report_map,user,report,photo,params,report_edit,comments,basic_data_and_functions) {
   var color_trad_text = {};
   color_trad_text['0'] = 'pas encore pris en compte (blanc)';
   color_trad_text['-1'] = 'rejeté (gris)';
   color_trad_text['1'] = 'pris en compte (rouge)';
   color_trad_text['2'] = 'en cours de résolution (orange)';
   color_trad_text['3'] = 'résolu (vert)';
   var current_report_id = null;

   function unactivateCommentMode() {
      /**
      * Return the map in the normal mode
      */
      $('#div_returnNormalMode').hide();
      $('#actions_panel').addClass('grid_5');
      $('#actions_panel').removeClass('grid_12');
      $('#map_container').show();
      $('#map_little').hide();
      $('#div__add_new_description').show();
      $('#div__filter_and_export').show();
      $('#div__latest_modifications').show();
      $('#div__zone_presentation').show();
      $('#div_last_private_comment_container').show();
      $('#span_plus_de_commenaitres_link').show();
      $('#div_list_private_comment_container').hide();
      $('#div_form_commentaires_cem_gestionnaire').hide();

      report_map.setTarget('map');
      report_map.undoCenterMapOnMarker();
   }

   function activateCommentsMode() {
      /**
      * Display description with the 'comments mode'
      */
      $('#div_returnNormalMode').show();
      $('#actions_panel').removeClass('grid_5');
      $('#actions_panel').addClass('grid_12');
      $('#map_container').hide();
      $('#map_little').show();
      $('#div__add_new_description').hide();
      $('#div__filter_and_export').hide();
      $('#div__latest_modifications').hide();
      $('#div__zone_presentation').hide();
      $('#div_last_private_comment_container').hide();
      $('#span_plus_de_commenaitres_link').hide();
      $('#div_list_private_comment_container').show();
      $('#div_form_commentaires_cem_gestionnaire').show();
      $('#add_new_report_form__message').val('');
      scroll(0,0);

      report_map.setTarget('map_little');

      if(current_report_id) {
         report_map.centerMapOnMarker(current_report_id);
      }
   }

   function display_description_of(id_desc) {
      /**
      * Function which display some data of the place on the webpage.
      To be executed when the user click on a marker on the index page.
      * @param {int} id_desc The id of the description.
      */
      var desc_data = report.get(id_desc);

      current_report_id = id_desc;
      photo.refresh_span_photo(id_desc);
      $('#link_add_photo').unbind('click');
      $('#link_add_photo').click(function() { photo.pop_up_add_photo(id_desc); });

      $('#div_edit_report__draw_error').hide();
      $('#span_edit_lon_lat_delete_error').hide();

      $('.class_span_report_description_id').each(function() { this.innerHTML = desc_data.id; });
      $('.class_span_report_description_loc').each(function() { this.innerHTML = desc_data.addressParts.road; });
      $('#input_report_description_id').val(desc_data.id);
      $('#span_report_description_signaleur').text(desc_data.creator.label);
      $('#span_report_description_creation_date').text(basic_data_and_functions.unixTimestamp2Date(desc_data.createDate.u));
      $('#span_report_description_loc').text(desc_data.addressParts.road);
      $('#span_report_description_desc').text(desc_data.description);

      if (desc_data.moderatorComment !== '' || user.isCeM() || user.isAdmin()) {
         $('#span_report_description_commentaireCeM').text(desc_data.moderatorComment);
         $('#div_container_report_description_commentaireCeM').show();
      } else {
         $('#span_report_description_commentaireCeM').text('');
         $('#div_container_report_description_commentaireCeM').hide();
      }

      $('#span_report_description_cat').text(desc_data.category.label);
      
      if ('manager' in desc_data && desc_data.manager !== null) {
         $('#span_report_description_gestionnaire').text(desc_data.manager.label);
      } else {
         $('#span_report_description_gestionnaire').text('pas encore de gestionnaire assigné');
      }

      if ('manager' in desc_data && desc_data.moderator) {
         $('#span_report_description_moderator').text(desc_data.moderator.label);
      } else {
         $('#span_report_description_moderator').text('pas encore de modérateur assigné');
      }

      $('#span_report_description_status').text(color_trad_text[0]);

      for (var i = 0; i < desc_data.statuses.length; i++) {
         if (desc_data.statuses[i].t == params.manager_color) {
            $('#span_report_description_status').text(color_trad_text[desc_data.statuses[i].v]);
         }
      }

      report_edit.stop_edition(); // si l'utilisateur a commencé à éditer , il faut cacher les formulaires
      display_regarding_to_user_role();
      
      $('#div__report_description_display').show();
   }

   function display_editing_button() {
      /**
      * if the user has certain role, he can edit certain information
      * this function display or not the button with which we can edit the 
      * information
      */
      if (user.canModifyCategory() || user.isAdmin()) {
         $('#span_report_description_cat_button').show();
      } else {
         $('#span_report_description_cat_button').hide();
      }

      if (user.canModifyLittleDetails() || user.isAdmin()) {
         $('#span_report_description_loc_button').show();
         $('#span_report_description_desc_button').show();
      } else {
         $('#span_report_description_loc_button').hide();
         $('#span_report_description_desc_button').hide();
      }

      if (user.canModifyPlacetype() || user.isAdmin()) {
         $('#span_report_description_type_button').show();
      } else {
         $('#span_report_description_type_button').hide();
      }

      if (user.canModifyManager() || user.isAdmin()) {
         $('#span_report_description_gestionnaire_button').show();
      } else {
         $('#span_report_description_gestionnaire_button').hide();
      }

      if(user.canModifyModerator() || user.isAdmin()) {
         $('#span_report_description_moderator_button').show();
      } else {
         $('#span_report_description_moderator_button').hide();
      }

      if (user.canUnpublishADescription() || user.isAdmin()) {
         $('#span_report_description_delete_button').show();
      } else {
         $('#span_report_description_delete_button').hide();
      }

      if (user.isCeM() || user.isAdmin()) {
         $('#span_report_description_commentaireCeM_button').show();
         $('#span_report_description_status_button').show();
         $('#div_container_report_description_commentaireCeM').show();
         $('#button_edit_lon_lat').show();
         $('#edit_report__draw_button').show();
      } else {
         $('#span_report_description_commentaireCeM_button').hide();
         $('#span_report_description_status_button').hide();
         $('#button_edit_lon_lat').hide();
         $('#edit_report__draw_button').hide();
      }

      if (user.isAdmin() || user.isCeM() || user.isGdV()) {
         $('#div_commentaires_cem_gestionnaire').show();
         $('#plus_de_commenaitres_link').unbind().on( 'click', function(e) { e.preventDefault(); activateCommentsMode(); });
      } else{
         $('#div_commentaires_cem_gestionnaire').hide();
      }

      $('#button_save_lon_lat').hide();
   }

   function display_regarding_to_user_role() {
      /**
      * The user can have the right to modify some information or to see personnal data
      of the creator.
      */
      if (user.isGdV() || user.isCeM() || user.isAdmin()) {
         comments.update_last(current_report_id);
         comments.update_all(current_report_id);
         $('#form_add_new_comment').unbind('click');
         $('#span_plus_de_commenaitres_link a').click(function(e) {
            e.preventDefault();
            activateCommentsMode();
         });

         $('#form_add_new_comment').unbind('submit');
         $('#form_add_new_comment').submit(function(e) {
            e.preventDefault();
            comments.submit_creation_form(current_report_id);
         });
      }

      if (user.canVieuwUsersDetails() || user.isAdmin()) {
         var desc_data = report.get(current_report_id);
         $('#span_report_description_signaleur_contact').html(' (email : <a href="mailto:'+ desc_data.creator.email +'">'+
         desc_data.creator.email +'</a>, téléphone : '+ desc_data.creator.phonenumber + ')');
      } else {
         $('#span_report_description_signaleur_contact').text('');
      }

      display_editing_button();
   }

   return {
      unactivateCommentMode: unactivateCommentMode,
      display_description_of: display_description_of,
      display_regarding_to_user_role: display_regarding_to_user_role
   };
});