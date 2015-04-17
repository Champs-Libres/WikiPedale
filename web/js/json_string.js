/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

define(['report_map','user'], function(report_map,user) {
   function unregisterUser(label,email,phonenumber){
      /**
      * Returns a json string describing an unregister user.
      * @param{string} label The label/pseudo of the user.
      * @param{string} email The email of the user.
      */
      return '{"entity":"user"' +
         ',"id":null' +
         ',"label":' + JSON.stringify(label) +
         ',"email":' + JSON.stringify(email) +
         ',"phonenumber":' + JSON.stringify(phonenumber) +
         '}';
   }

   function point(lon,lat){
      /**
      * Returns a json string describing a point.
      * @param{string} lon The longitude of the point.
      * @param{string} lat} The latitude of the point.
      */
      return '{"type":"Point","coordinates":[' + lon + ',' + lat + ']}';
   }

   function changeReport(id, changement){
      /**
      * Returns a json string describing a report.
      * @param{int} id The id of the report.
      * @param{string} changement A json string representing the changement to do.
      */
      var ret = '{"entity":"report"';
      ret = ret + ',"id":' + JSON.stringify(id) + ',';
      ret = ret + changement;
      return ret + '}';
   }

   function editModeratorComment(id,new_moderator_comment){
      /**
      * Returns a json for editing the moderator comment of a report.
      * @param{int} id The id of the report.
      * @param{string} new_moderator_comment The new moderator comment.
      */
      return changeReport(id,'"moderatorComment":' + JSON.stringify(new_moderator_comment));
   }

   function editDescription(id,new_description){
      /**
      * Returns a json for editing the parameter 'description' of a report.
      * @param{int} id The id of the report.
      * @param{string} new_description The new value of the parameter 'description'.
      */
      return changeReport(id,'"description":' + JSON.stringify(new_description));
   }

   function editLocation(id,new_location){
      /**
      * Returns a json for editing location of a report.
      * @param{int} id The id of the report.
      * @param{string} new_location The new location.
      */
      return changeReport(id,'"addressParts":{"entity":"address","road":' + JSON.stringify(new_location) + '}');
   }

   function editCategory(id, new_category_id){
      /**
      * Returns a json for editing the category (single) of a report.
      * @param{int} id The id of the report.
      * @param{int} new_category_id The new category id.
      */
      return changeReport(id,'"category":{"entity":"category","id":' + new_category_id + '}');
   }

   function editStatus(id,status_type,new_status_value){
      /**
      * Returns a json for editing the status of a report.
      * @param{int} id The id of the report.
      * @param{string} status_type The type of the status
      * @param{string} new_status_value The new value of the status.
      */
      return changeReport(id,'"statuses":[{"t":"' + status_type + '","v":"' + new_status_value + '"}]');
   }

   function editManager(id,new_manager_id){
      /**
      * Returns a json for editing the manager of a report.
      * @param{int} id The id of the report.
      * @param{int} new_manager_id The id of the new manager.
      */
      return changeReport(id,'"manager": {"entity":"group","type":"MANAGER","id":' +
         JSON.stringify(new_manager_id)  + '}');
   }

   function editModerator(id,new_moderator_id){
      /**
      * Returns a json for editing the moderator of a report.
      * @param{int} id The id of the report.
      * @param{int} new_moderator_id The id of the new moderator.
      */
      return changeReport(id,'"moderator": {"entity":"group","type":"MODERATOR","id":' +
         JSON.stringify(new_moderator_id)  + '}');
   }

   function editReportType(id, new_placetype_id){
      /**
      * Returns a json for editing place type of a report.
      * @param{int} id The id of the report.
      * @param{int} new_placetype_id The new id of the place type.
      */
      return changeReport(id,'"placetype":{"id":' +  JSON.stringify(new_placetype_id) + ',"entity":"placetype"}');
   }

   function editReportPosition(id,lon,lat) {
      /**
      * Returns a json for editing the position of a report.
      * @param{int} id The id of the report.
      * @param{int} lon the new longitude of the report.
      * @param{int} lat the new latitude of the report.
      */
      return changeReport(id,'"geom":'+ point(lon,lat));
   }


   function editReportDrawings(id, drawn_geojson) {
      /**
      * Returns a json for the drawings of a report.
      * @param{int} id The id of the report.
      * @param{object} drawn_geojson The geojson of the drawings.
      */
      return changeReport(id, '"drawnGeoJSON":' + JSON.stringify(drawn_geojson));
   }

   function deleteReport(id){
      /**
      * Returns a json for deleting a report.
      * @param{int} id The id of the report to delete.
      */
      return changeReport(id,'"accepted":false');
   }

   function newReport(description, lon, lat, address, id, color, user_label, user_email, user_phonenumber, category, drawn_geojson) {
      /**
      * Returns a json string used for adding a new report.
      *
      * @param {string} description the description of the new report.
      * @param {string} lon The longitude of the new report.
      * @param {string} lat The latitude of the new report.
      * @param {string} address The address of the new report.
      * @param {string} id The id of the new report, this parameter is optionnal : if it isn't given or null it means tha the report is a new one.
      * @param {string} color The color of the report (only for existing report)
      * @param {string} user_label The label given by the user : if the user is register and logged this field is not considered
      * @param {string} user_email The email given by the user : if the user is register and logged this field is not considered
      * @param {string} user_phonenumber The phonenumber given by the user : if the user is register and logged this field is not considered
      * @param {int} caterogy The id of the selected category
      */
      var ret = '{"entity":"report"';

      if (typeof id === 'undefined' || id===null) {
         ret = ret + ',"id":null';
      } else {
         ret = ret + ',"id":' + JSON.stringify(id);
      }

      if (typeof lon !== 'undefined' && lon!==null && typeof lat !== 'undefined' && lat!==null) {
         ret = ret + ',"geom":'+ point(lon,lat);
      }

      if ( !user.isRegistered() && (typeof user_label !== 'undefined' || typeof user_email !== 'undefined')) {
         ret = ret + ',"creator":' + unregisterUser(user_label, user_email, user_phonenumber);
      }

      ret = ret + ',"description":' + JSON.stringify(description) +
         ',"addressParts":{"entity":"address","road":' + JSON.stringify(address) + '}';

      ret = ret + ',"drawnGeoJSON":' + JSON.stringify(drawn_geojson);

      ret = ret + ',"category":{"entity":"category","id":' + category + '}';
      return ret + '}';
   }

   function moderatorManagerComment(report_id, comment_text) {
      /**
       * Return a json string use for adding/editing a new moderator-manager comment.
       * @param {int} reportId The id of the report.
       * @param {string} comment_text The text of the comment.
       * @return {sring} The json string describing the comment.
       */
      return '{"entity":"comment","reportId":' + JSON.stringify(report_id) +
      ',"text":' + JSON.stringify(comment_text) + ',"type":"moderator_manager"}';
   }

   return {
      editModeratorComment: editModeratorComment,
      editDescription: editDescription,
      editLocation: editLocation,
      editCategory: editCategory,
      editStatus: editStatus,
      editManager: editManager,
      editModerator: editModerator,
      editReportType: editReportType,
      deleteReport: deleteReport,
      newReport: newReport,
      editReportPosition: editReportPosition,
      moderatorManagerComment: moderatorManagerComment,
      editReportDrawings: editReportDrawings,
   };
});