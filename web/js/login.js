/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module contains all the function used when a user want to log in.
*/
define(['jQuery','data_map_glue','user'], function($,data_map_glue,user) {
   function display_login_form_with_message(message){
      /**
      * Display the login form in a colorbox
      * @param {string} the message to be displayeed
      */
      $('#login_message').text(message);
      $.colorbox({inline:true, href:'#login_form_div'});
   }

   function catch_form(){
      /**
      * To be excecuted when the login form is submitted.
      * This function checks asking to the db if couple username/password is correct
      */
      var authenticate_url = Routing.generate('wikipedale_authenticate', {_format: 'json'});
      var username = $('#login_input_username').val();
      var password = $('#login_input_password').val();

      function make_base_auth(user, password) {
         var tok = user + ':' + password;
         var hash = btoa(tok);
         return 'Basic ' + hash;
      }
      
      $.ajax ({
         type: 'GET',
         beforeSend: function (xhr){
            xhr.setRequestHeader('Authorization', make_base_auth(username, password));
         },
         url: authenticate_url,
         dataType: 'json',
         cache: false,
         success: function(output_json) {
            var ret_user;
            if (! output_json.query.error) {
               ret_user = output_json.results[0];
               if(! ret_user.registered) {
                  $('#login_message').text('Login failed');
                  $('#login_message').addClass('errorMessage');
               } else {
                  user.update(ret_user);
                  update_page_when_logged();
               }
            } else {
               $('#login_message').text(output_json[0].message);
               $('#login_message').addClass('errorMessage');
            }
         },
         error: function() {
            $('#login_message').text('Login failed');
            $('#login_message').addClass('errorMessage');
         }
      });
   }

   function update_page_when_logged(){
      /**
      * Updates the menu when the user is logged :
      * - connexion link and register link : disappear
      * - user name and logout link : appear
      */
      $('#menu_user_name').css('display', 'inline-block');
      $('#menu_connexion').hide();
      $('#menu_logout').css('display', 'inline-block');
      $('#menu_register').hide();

      $('#div_new_report_form_user_mail').hide();

      $('a.connexion').colorbox.close('');
      $('.username').text(user.data().label);

      data_map_glue.updateDataAndMap();
   }

   return {
      display_login_form_with_message: display_login_form_with_message,
      catch_form: catch_form
   };
});