/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	config.extraPlugins = 'eqneditor,bbcode,base64image';

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbar = [
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'insert', items: ['Image', 'EqnEditor', /*'Smiley' <-- bbcode plugin will convert it to chars*/, 'SpecialChar'] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', '-', 'RemoveFormat'] },
		{ name: 'paragraph', items: ['NumberedList', 'BulletedList' , '-', 'Blockquote'] },
		{ name: 'links', items: ['Link', 'Unlink'] },
		// { name: 'styles', items: ['FontSize'] }, <-- bbcode plugin may have bugs with this
		{ name: 'colors', items: ['TextColor'] },
		{ name: 'about', items: ['About'] }
	];

	// Se the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Make dialogs simpler.
	config.removeDialogTabs = 'image:advanced;link:advanced';
};
