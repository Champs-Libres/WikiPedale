/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module is used when the user want to create a new description (used to catch the 
creating form and to clear this form)
*/
define(['jQuery','basic_data_and_functions','map_display','data_map_glue','informer','user','json_string','descriptions','login'],
      function($, basic_data_and_functions,map_display,data_map_glue,informer,user,json_string,descriptions,login) {
   function catch_creating_form(the_form_to_catch) {
      /**
      * Catches the form used to create a new description.
      * @param {DOM elem} the_form_to_catch the DOM elem which is the form to catch.
      * This element must contain a div with an element of class '.message' where to 
      * display the error and success messages.
      */
      var desc_data = {},
         error_messages = '',
         messages_div = $('#add_new_description_form__message');

      desc_data['categories'] = [];

      $.map($(the_form_to_catch).serializeArray(), function(n){
         if (n['name'] === 'categories') {
            desc_data['categories'].push(n['value']);
         } else {
            desc_data[n['name']] = n['value'];
         }
      });

      if(desc_data['description'] === '') {
         error_messages = error_messages + 'Veuillez remplir la description. ';
      }

      if(desc_data['lieu'] === '') {
         error_messages = error_messages + 'Veuillez indiquer l\'adresse. ';
      }

      if(desc_data['lon'] === '' || desc_data['lat'] === '') {
         error_messages = error_messages + 'Veuillez indiquer où se trouve le point noir en cliquant sur la carte. ';
      }

      if(! user.isRegistered()){
         if(desc_data['user_label'] === '') {
            error_messages = error_messages + 'Veuillez donner votre nom. ';
         }

         if(! basic_data_and_functions.is_mail_valid(desc_data['email'])) {
            error_messages = error_messages + 'Veuillez indiquer une adresse email valide. ';
         }
      }

      user.isInAccordWithServer().done(function(userInAccordWithServer) {
         if(!userInAccordWithServer) {
            login.display_login_form_with_message('Veuillez vous reconnecter.');
         } else {
            if(error_messages !== '') {
               $(messages_div).text('Erreur! ' + error_messages  + 'Merci.');
               $(messages_div).addClass('errorMessage');
            } else {
               $(messages_div).text('Traitement en cours');
               var entity_string = json_string.edit_place(desc_data['description'], desc_data['lon'],
                  desc_data['lat'], desc_data['lieu'], desc_data['id'], desc_data['couleur'],
                  desc_data['user_label'], desc_data['email'], desc_data['user_phonenumber'],desc_data['categories']);
               var url_edit = Routing.generate('wikipedale_report_change', {_format: 'json'});
               $.ajax({
                  type: 'POST',
                  data: {entity: entity_string},
                  url: url_edit,
                  cache: false,
                  success: function(output_json) {
                     if(! output_json.query.error) {
                        var newPlaceData = output_json.results[0];
                        clear_creating_form();
                        if(user.isRegistered()) { //sinon verif de l'email 
                           $(messages_div).text('Le point noir que vous avez soumis a bien été enregistré. Merci!');
                           if($('#map').attr('class') === 'olMap') {
                              setTimeout( function(){
                                 data_map_glue.add_marker_and_description(newPlaceData);
                                 data_map_glue.mode_change();
                                 data_map_glue.focus_on_place_of(newPlaceData.id);
                                 map_display.delete_marker_for('new_description');
                              },3000);
                           }
                        } else {
                           $(messages_div).text('Le point noir que vous avez soumis a bien été enregistré. Avant d\'afficher le point noir, nous allons vérifier votre adresse mail. Veuillez suivre les instructions qui vous ont été envoyées par email.');
                           if($('#map').attr('class') === 'olMap') {
                              setTimeout(
                                 function(){
                                    data_map_glue.mode_change();
                                 },3000);
                           }
                        }

                        if($('#map').attr('class') !== 'olMap') {
                           $('#form__add_new_description').hide();
                           $('#new_report_form_submit_button').hide();
                           $('#show_map_button').hide();
                           $('#new_place_form_menu_return_button').show();
                        }
                        $(messages_div).addClass('successMessage');
                     } else {
                        alert('Mince, il y a un problème. Veuillez nous le signaler. Merci');
                     }
                  },
                  error: function() {
                     alert('Mince, il y a un problème. Veuillez nous le signaler. Merci');
                  }
               });
            }
         }
      });
   }

   function clear_creating_form() {
      /** 
      * Clear the data entered in the form used to create new description.
      * It remove also the marker of the map.
      */
      $('#form__add_new_description input[type=text], #form__add_new_description textarea, #form__add_new_description input[type=hidden]').val('');
      $('#form__add_new_description').children('.message').text('');
      if($('#map').attr('class') === 'olMap') {
         informer.reset_new_description_form();
         map_display.delete_marker_for('new_description');
      }
   }

   return {
      catch_creating_form: catch_creating_form,
      clear_creating_form: clear_creating_form
   };
});