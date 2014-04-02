/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

define(['jQuery'], function($) {
   function filling(aCitySlug,nbr_max){
      var jsonUrlData  =  Routing.generate('wikipedale_history_report_by_city', {_format: 'json', citySlug: aCitySlug, max:nbr_max});
      $.ajax({
         dataType: 'json',
         url: jsonUrlData,
         success: function(data) {
            $.each(data.results, function(index, aLastModif) {
               $('#div_content_dernieres_modifs').append(aLastModif.text);
               var lien_voir = $(document.createElement('a'))
                  .text('(voir)')
                  .attr('href', '?id=' + aLastModif.reportId);
               $('#div_content_dernieres_modifs').append(lien_voir);
               $('#div_content_dernieres_modifs').append('<br>');
            });
         }
      });
   }

   return {
      filling: filling
   };
});