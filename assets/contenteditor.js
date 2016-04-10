var editor = ContentTools.EditorApp.get();
editor.init('*[data-editable]', 'data-file');

editor.addEventListener('saved', function (ev) {

    this.busy(true);
    regions = ev.detail().regions;

    for (name in regions) {
        if (regions.hasOwnProperty(name)) {
            $.request("contenteditor::onSave", {
                data: {
                    file: name,
                    content: regions[name]
                }
            });
        }
    }

    new ContentTools.FlashUI('ok');
    setTimeout(function(){
        editor.busy(false);
    }, 600);

});
