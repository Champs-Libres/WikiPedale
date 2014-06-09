/* jslint vars: true */
/*jslint indent: 3 */
/* global require */
'use strict';

require.config({
   paths: {
      'jQuery': 'bower_components/jquery/jquery',
      'OpenLayers': 'bower_components/OpenLayers/OpenLayers',
      'select2': 'bower_components/select2/select2',
      'colorbox': 'bower_components/colorbox/jquery.colorbox',
   },
   shim: {
      'jQuery': {
         exports: '$'
      },
      'OpenLayers': {
         exports: 'OpenLayers'
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

require(['jQuery','recent_activities','data_map_glue','informer','markers_filtering','select2','colorbox','description_create','map_display','login','description_text_display','description_edit', 'category'],
   function($,recent_activities,data_map_glue,informer,markers_filtering,select2,colorbox,description_create,map_display,login,description_text_display,description_edit, category){
      $.ajaxSetup({ cache: false }); // IE save json data in a cache, this line avoids this behavior
      //Category
      category.init();

      $(document).ready(function(){
         $('a.connexion').colorbox({
            inline:true,
            width:'400px',
            height:'400px',
            onComplete: function(){ $('#login_input_username').focus(); }
         });

         //Login
         $('#loginForm').submit(function(e) { e.preventDefault(); login.catch_form(); });

         var data_for_init = $('#data_for_init');
         if (data_for_init.length !== 0)
         {
            var city_name = data_for_init.attr('data-city');
            var city_lon = data_for_init.attr('data-lon');
            var city_lat = data_for_init.attr('data-lat');
            var description_selected_id = data_for_init.attr('data-description_selected_id');

            if(typeof description_selected_id === 'undefined') {
               description_selected_id=null;
            }

            recent_activities.filling(city_name,5);
            data_map_glue.init_app(city_name, city_lon, city_lat,description_selected_id);

            //Category Filtering
            category.insertParentCategoryToSelectField('#optionsAffichageFilterCategoriesParent', ['short','medium']);
            $('#optionsAffichageFilterCategoriesParent').select2();
            $('#optionsAffichageFilterCategoriesChildren').select2();
            $('#optionsAffichageFilterCategoriesParent').on('select2-selecting', function(e) {
               category.setChildrenToSelect2Filed('#optionsAffichageFilterCategoriesChildren',e.val, ['short','medium']);
               $('#optionsAffichageFilterCategoriesChildren').on('change', markers_filtering.display_markers_regarding_to_filtering);
               markers_filtering.display_markers_regarding_to_filtering();
            });

            $('#optionsAffichageFilterCategoriesParent').select2('disable');
            $('#optionsAffichageFilterCategoriesChildren').select2('disable');

            category.insertParentCategoryToSelectField('#optionsAffichageAddLongTermCategoriesParent', ['long']);
            $('#optionsAffichageAddLongTermCategoriesParent').select2();
            $('#optionsAffichageAddLongTermCategoriesChildren').select2();
            $('#optionsAffichageAddLongTermCategoriesParent').on('select2-selecting', function(e) {
               category.setChildrenToSelect2Filed('#optionsAffichageAddLongTermCategoriesChildren',e.val, ['long']);
               $('#optionsAffichageAddLongTermCategoriesChildren').on('change', markers_filtering.display_markers_regarding_to_filtering);
               markers_filtering.display_markers_regarding_to_filtering();
            });

            $('#optionsAffichageAddLongTermCategoriesParent').select2('disable');
            $('#optionsAffichageAddLongTermCategoriesChildren').select2('disable');

            $('input[name=affichage_tous_ou_filtre_categorie]').click(function() { markers_filtering.change_mode_for('FilterCategories'); } );
            $('input[name=affichage_tous_ou_filtre_pn_categorie]').click(function() { markers_filtering.change_mode_for('AddLongTermCategories'); } );



            //Cem Filtering
            $('#optionsAffichageFilterStatusCeM').select2();
            $('#optionsAffichageFilterStatusCeM').select2('disable');

            $('#span_report_description_cat_edit').select2();
            $('#span_report_description_status_edit').select2();
            $('#span_report_description_type_edit').select2();
            $('#span_report_description_gestionnaire_edit').select2();

            $('#add_new_description_form__category').select2().on('change', function() { informer.update_new_description_form('category'); });

            $('#optionsAffichageFilterStatusCeM').on('change', markers_filtering.display_markers_regarding_to_filtering);

            $('#div_returnNormalMode').hide();

            $('input[name=affichage_tous_ou_filtre_statusCeM]').click(function() { markers_filtering.change_mode_for('FilterStatusCeM'); } );
            $('input[name=affichage_statusCeM_rejete]').click(function() { markers_filtering.change_mode_for('AddStatusCeMRejete'); } );

            //Timestamp Filtering
            $('#timestampFilteringCheckbox').click(function() { markers_filtering.change_mode_for('timestamp'); } );
            $('#optionsAffichageFilterTimestampFrom').on('change', markers_filtering.display_markers_regarding_to_filtering);
            $('#optionsAffichageFilterTimestampTo').on('change', markers_filtering.display_markers_regarding_to_filtering);

            
            // Menu
            $('#div_add_new_description_button').click(function() { data_map_glue.mode_change(); });
            $('#div_add_new_description_cancel_button').click(function() { data_map_glue.mode_change(); });
            $('#div_returnNormalMode').click(function() { map_display.normal_mode(); });
            $('#buttonOptionsAffichage').click(function() { markers_filtering.activate_unactivate_filtering_form(); } );
            $('#buttonOptionsAffichage_cancel').click(function() { markers_filtering.activate_unactivate_filtering_form(); } );


            // Add New Description
            $('#add_new_description_form__user_label').blur(function() { informer.update_new_description_form('user_label'); });
            $('#add_new_description_form__email').blur(function() { informer.update_new_description_form('email'); });
            $('#add_new_description_form__user_phonenumber').blur(function() { informer.update_new_description_form('user_phonenumber'); });
            $('#add_new_description_form__lieu').blur(function() { informer.update_new_description_form('lieu'); });
            $('#add_new_description_form__description').blur(function() { informer.update_new_description_form('description'); });
            $('#form__add_new_description').submit(function(e) { e.preventDefault(); description_create.catch_creating_form(this); });
            $('#new_place_form_reset_button').click(function(e) { e.preventDefault(); description_create.clear_creating_form(); });
            $('#add_new_description_form_informer__category_medium_warning').hide();


            //Place Description Edit
            $('#span_report_description_loc_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('loc'); });
            $('#span_report_description_desc_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('desc'); });
            $('#span_report_description_commentaireCeM_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('commentaireCeM'); });
            $('#span_report_description_cat_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('cat'); });
            $('#span_report_description_status_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('status'); });
            $('#span_report_description_type_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('type'); });
            $('#span_report_description_gestionnaire_button').click(function(e) { e.preventDefault();  description_edit.description_edit_or_save('gestionnaire'); });
            $('#span_report_description_delete_button').click(function(e) {e.preventDefault(); data_map_glue.last_description_selected_delete(); });
            $('span_plus_de_commenaitres_link a').click(function(e) { e.preventDefault(); description_text_display.activate_comments_mode(); });
            $('#button_edit_lon_lat').click(function(e) { e.preventDefault(); description_edit.position_edit_or_save(); });
            $('#button_save_lon_lat').click(function(e) { e.preventDefault(); description_edit.position_edit_or_save(); });
         
            markers_filtering.display_markers_regarding_to_filtering();
         }
      });
   });