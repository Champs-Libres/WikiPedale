/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/**
* Dealing with the categories :
* - storage in JS
*/

define(['jQuery'], function($) {
   var categories; // the known categories

   /**
    * Load the categories
    * @return No return
    */
   function init() {
      $.get(Routing.generate('public_category_list_all_parent_children', {_format: 'json'}), function( data ) {
         if(! data.query.error) {
            categories = data.results;

            $.each(categories, function(i,cat) {
               cat.by_term_children = {};
               $.each(cat.children, function(i, child) {
                  if(! (child.term in cat.by_term_children)) {
                     cat.by_term_children[child.term] = [];
                  }

                  cat.by_term_children[child.term].push(child);
               });
            });
         }
      });
   }

   /**
    * Get all the categories
    * @param {function} callback A callback function work on the categories
    * @return No return
    */
   function getAll(callback) {
      if(categories) {
         return callback(categories);
      } else {
         setTimeout(function() { getAll(callback); }, 500);
      }
   }

   /**
    * Get a category
    * @param {int} id The id of the category
    * @return The category
    */
   function getCategory(id) {
      var i, caterories_len;
      for(i = 0, caterories_len = categories.length; i < caterories_len; i++) {
         if(parseInt(categories[i].id) === parseInt(id)) {
            return categories[i];
         }
      }
      return null;

   }

   /**
    * Set the parents categories into a select field (filter by terms)
    * @param {string} select_id The html id of the select field
    * @param {Array of string} terms The terms to filter :
    * a parent category is added if
    * - one of its child has its term in terms
    * - its has no children and its term ins in temrs
    * @return No return
    */
   function insertParentCategoryToSelectField(select_id, terms) {
      getAll(function(categories) {
         $(select_id).html('');

         $.each(categories, function(i,cat) {
            var display_cat = false;
            $.each(terms, function(i, term) {
               if(term in cat.by_term_children) {
                  display_cat = true;
               }
            });

            if(display_cat || (cat.children.length === 0 && terms.indexOf(cat.term) !== -1)) {
               $(select_id).append('<option value="' + cat.id +  '">' + cat.label + '</option>');
            }
         });
      });
   }

   /**
    * Set the children categories into a select field (filter by parent and term)
    * @param {string} select_id The html id of the select field
    * @param {int} parent_id The id of the parent category
    * @param {Array of string} terms The terms to filter : 
    *    only the category have term in terms are added
    * @return No return
    */
   function setChildrenToSelect2Filed(select_id, parent_id, terms){
      var parent_cat = getCategory(parent_id);
      $(select_id).select2('destroy');
      $(select_id).html('');

      if(parent_cat.children.length === 0) { //pas d'enfants
         $(select_id).append('<option value="' + parent_cat.id +  '"selected="select">' + parent_cat.label + '</option>');
      } else {
         $.each(terms, function(i,term) {
            if(term in parent_cat.by_term_children) {
               $.each(parent_cat.by_term_children[term], function(i, child) {
                  $(select_id).append('<option value="' + child.id +  '"selected="select">' + child.label + '</option>');
               });
            }
         });
      }
      $(select_id).select2();
   }

   return {
      init: init,
      getAll: getAll,
      insertParentCategoryToSelectField: insertParentCategoryToSelectField,
      setChildrenToSelect2Filed: setChildrenToSelect2Filed
   };
});