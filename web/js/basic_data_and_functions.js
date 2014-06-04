/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module contains basic data and functions.
*/

define([], function() {
   var web_dir = ''; // the URL where app.php is contained
   var baseUrlsplit = Routing.getBaseUrl().split('/');
   var i = 0;
   for (i = 0; i < (baseUrlsplit.length - 1);  i++) {
      web_dir = web_dir + baseUrlsplit[i] + '/';
   }

   function nl2br (str, is_xhtml) {
      // http://kevin.vanzonneveld.net
      // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // +   improved by: Philip Peterson
      // +   improved by: Onno Marsman
      // +   improved by: Atli Þór
      // +   bugfixed by: Onno Marsman
      // +      input by: Brett Zamir (http://brett-zamir.me)
      // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // +   improved by: Brett Zamir (http://brett-zamir.me)
      // +   improved by: Maximusya
      // *     example 1: nl2br('Kevin\nvan\nZonneveld');
      // *     returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
      // *     example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
      // *     returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
      // *     example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
      // *     returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'
      var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

      return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
   }

   function is_mail_valid(anEmail) {
      /**
      * Returns True/False if the email is valid
      * @param{string} anEmail The considered email 
      */
      var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
      return(reg.test(anEmail));
   }


   var months = ['Janvier','Février','Mars','Avril','Mai','Jun','Jullet','Août','Septembre','Octobre','Novembre','Décembre'];

   /**
   * Returns a human readable date form a timestamp
   * @param{unix timestamp} t The unix timestamp (used in php)
   */
   function unixTimestamp2Date(t) {
      var d = new Date(t * 1000);
      return '' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
   }

   /**
   * Returns the unix timestamp for a DD/MM/YYYY-format string (DD/MM/YY-format is supporter)
   * @param{string} s The DD/MM/YYYY-format string that represents the date
   * @param{?} error_value The value to return when the given string is not in the DD/MM/YYYY-format (DD/MM/YY-format is supporter)
   * @param{boolean} end_of_day true if the timestamp must refer to the end of the day 23:59, otherwise the timestamp refers to 00:00
   */
   function stringDate2UnixTimestamp(s, error_value, end_of_day) {
      var date, year, s_split = s.split('/');

      if(s_split.length === 3 && !isNaN(s_split[0]) && !isNaN(s_split[1]) && !isNaN(s_split[2]))
      {
         year = s_split[2]; //support of DD/MM/YY-format
         if(year.length == 2) {
            if(parseInt(year) <= 15) {
               year = parseInt('20' + year);
            } else {
               year = parseInt('19' + year);
            }
         }

         if(end_of_day) {
            date = new Date(year,parseInt(s_split[1]) - 1, parseInt(s_split[0]), 23, 59, 59, 999);
         } else {
            date = new Date(year,parseInt(s_split[1]) - 1, parseInt(s_split[0]), 0, 0, 0, 0);
         }
         return date.getTime() / 1000;
      } else  {
         return error_value;
      }
   }

   return {
      web_dir: web_dir, // the URL where app.php is contained
      nl2br: nl2br,
      is_mail_valid: is_mail_valid,
      unixTimestamp2Date: unixTimestamp2Date,
      stringDate2UnixTimestamp: stringDate2UnixTimestamp
   };
});