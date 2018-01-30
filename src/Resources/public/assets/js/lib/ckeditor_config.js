CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
    // The toolbar groups arrangement, optimized for a single toolbar row.
    config.toolbarGroups = [
        { name: 'clipboard',   groups: [ 'undo' ] },
        { name: 'basicstyles', groups: [ 'basicstyles' ] },
        { name: 'links' },
        { name: 'styles' }
    ];

    // The default plugins included in the basic setup define some buttons that
    // are not needed in a basic editor. They are removed here.
    config.removeButtons = 'Cut,Copy,Paste,Anchor,Underline,Strike,Subscript,Superscript,Font,Styles';

    // Dialog windows are also simplified.
    config.removeDialogTabs = 'link:advanced';
};