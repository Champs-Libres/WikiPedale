/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module is used when the user want to create a new report (used to catch the 
creating form and to clear this form)
*/
define(['jQuery','basic_data_and_functions','report_map','data_map_glue','informer','user','json_string','report','login'],
      function($, basic_data_and_functions,report_map,data_map_glue,informer,user,json_string,report,login) {
   var messages_div = $('#add_new_report_form__message');

   function catchCreatingForm(form_to_catch) {
      /**
      * Catches the form used to create a new description. This function check
      * if the coordinates of the new report are valid (are in a moderated zone)
      * @param {DOM elem} form_to_catch the DOM elem which is the form to catch.
      * This element must contain a div with an element of class '.message' where to 
      * display the error and success messages.
      */
      var desc_data = {};

      $.map($(form_to_catch).serializeArray(), function(n){
         desc_data[n.name] = n.value;
      });

      checkCoordinatesThen(desc_data, function() {
         catchCreatingFormWithValidCoordinates(desc_data);
      });
   }

   function catchCreatingFormWithValidCoordinates(desc_data) {
      /**
       * Catches the form used to create a new report.
       * @param {array} desc_data The data entered in the form
       * This element must contain a div with an element of class '.message' where to 
       * display the error and success messages.
       */
      var error_messages = '';

      if(desc_data.description === '') {
         error_messages = error_messages + 'Veuillez remplir la description. ';
      }

      if(desc_data.lieu === '') {
         error_messages = error_messages + 'Veuillez indiquer l\'adresse. ';
      }

      if(desc_data.lon === '' || desc_data.lat === '') {
         error_messages = error_messages + 'Veuillez indiquer où se trouve le point noir en cliquant sur la carte. ';
      }

      if(! user.isRegistered()){
         if(desc_data.user_label === '') {
            error_messages = error_messages + 'Veuillez donner votre nom. ';
         }

         if(! basic_data_and_functions.is_mail_valid(desc_data.email)) {
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
               var entity_string = json_string.edit_place(desc_data.description, desc_data.lon,
                  desc_data.lat, desc_data.lieu, desc_data.id, desc_data.couleur,
                  desc_data.user_label, desc_data.email, desc_data.user_phonenumber,desc_data.category,
                  report_map.getDrawnDetails('new_report'));
               var url_edit = Routing.generate('wikipedale_report_change', {_format: 'json'});
               $.ajax({
                  type: 'POST',
                  data: {entity: entity_string},
                  url: url_edit,
                  cache: false,
                  success: function(output_json) {
                     if(! output_json.query.error) {
                        var new_report = output_json.results[0];
                        clearCreatingForm();
                        if(user.isRegistered()) { //sinon verif de l'email 
                           $(messages_div).text('Le point noir que vous avez soumis a bien été enregistré. Merci!');
                           setTimeout( function(){
                              report_map.addReport(new_report);
                              data_map_glue.modeChange();
                              data_map_glue.focusOnReport(new_report.id);
                              report_map.deleteMarker('new_report');
                              $(messages_div).text('');
                              $(messages_div).removeClass('successMessage');
                           },3000);
                        } else {
                           $(messages_div).text('Le point noir que vous avez soumis a bien été enregistré. Avant d\'afficher le point noir, nous allons vérifier votre adresse mail. Veuillez suivre les instructions qui vous ont été envoyées par email.');
                           setTimeout(
                              function(){
                                 data_map_glue.modeChange();
                                 report_map.deleteMarker('new_report');
                              },3000);
                        }

                        $(messages_div).addClass('successMessage');
                     } else {
                        console.log(output_json);
                        $(messages_div).text('Mince, il y a un problème. Veuillez nous le signaler. Merci.');
                        $(messages_div).addClass('errorMessage');
                     }
                  },
                  error: function(error_message) {

                     $(messages_div).text('Mince, il y a un problème : ' +
                        error_message.responseText +
                        '. Si le problème persiste, veuilllez nous le signaler. Merci.');
                     $(messages_div).addClass('errorMessage');
                  }
               });
            }
         }
      });
   }

   function checkCoordinatesThen(desc_data, callback) {
      /**
       * Check if the given lat / lang are valid (beeing in a zone moderated)
       * and if it is the case execute a callback.
       * @param {array} desc_data The data entered in the form
       * @param {function} callback The callback to execute if the entered coordinates are valids
       * @return No return
       */
      $.getJSON(
         Routing.generate('wikipedale_zone_view_covering_point', {lon: desc_data.lon, lat: desc_data.lat, _format: 'json'}),
         function (data) {
            if(data.results.length === 0) {
               $(messages_div).text('Erreur! Le signalement introduit ne se trouve dans aucune zone gérée par l\'outil!');
               $(messages_div).addClass('errorMessage');
            } else {
               callback();
            }
         }
      );
   }

   function clearCreatingForm() {
      /** 
      * Clear the data entered in the form used to create new description.
      * It remove also the marker of the map.
      */
      $('#form__add_new_description input[type=text], #form__add_new_description textarea, #form__add_new_description input[type=hidden]').val('');
      $('#form__add_new_description').children('.message').text('');
      informer.reset_new_description_form();
      report_map.deleteMarker('new_report');
   }

   return {
      catchCreatingForm: catchCreatingForm,
      clearCreatingForm: clearCreatingForm
   };
});