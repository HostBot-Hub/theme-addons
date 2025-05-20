const { registerBlockType } = wp.blocks;

import breadcrumbsEditView from './breadcrumbs';
import PinTackEditView from './pin-tack';
import AttachmentEditView from './attachment-view';


const blocks = [
     {
          name: 'custom/breadcrumbs',
          title: 'Breadcrumbs',
          icon: 'image-filter',
          category: 'widgets',
          edit: breadcrumbsEditView,
          save: function(props) {
               return null;
          }
     },
     {
          name: 'custom/pintack',
          title: 'Pin Tack Graphic',
          icon: 'image-filter',
          category: 'widgets',
          edit: PinTackEditView,
          save: function(props) {
               return null;
          }
     },
     {
          name: 'custom/attachment-view',
          title: 'Attachment Preview',
          icon: 'image-filter',
          category: 'widgets',
          edit: AttachmentEditView,
          save: function(props) {
               return null;
          }
     }
];


blocks.forEach(block => {

     registerBlockType(block.name, {
          title: block.title,
          icon: block.icon,
          category: block.category,
          edit: block.edit,
          save: block.save
     });
});