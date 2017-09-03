/*
* Load ContentTools
*/
var editor = ContentTools.EditorApp.get();
editor.init('[data-editable], [data-fixture]', 'data-file');

var siteUrl = document.location.origin; // get site url for requests

/*
* Save event
*/
editor.addEventListener('saved', function (ev) {

    this.busy(true);
    regions = ev.detail().regions;

    for (name in regions) {

        if (regions.hasOwnProperty(name))
        {
            var component = $('*[data-file="' + name + '"]').data('component'); // check for component name
            $.request(component, {
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

/*
* Fixture focus
*/
ContentEdit.Root.get().bind('focus', function(element) {
    var dataTools = element._parent._domElement.dataset.tools
    var tools;
    switch (dataTools) {
        case '':
            tools = editor._toolbox._tools;
            break;
        case '*':
            tools = ContentTools.DEFAULT_TOOLS;
            break;
        default:
            tools = [element._parent._domElement.dataset.tools.split(',')];
    }
    if (element.isFixed()) tools = dataTools !== '*' ? tools : [['undo', 'redo', 'remove']];
    if (editor.toolbox().tools() !== tools) editor.toolbox().tools(tools);
});

/*
* Predefined tools
*/
var __hasProp = {}.hasOwnProperty;
var __extends = function(child, parent) {
    for (var key in parent) {
        if (__hasProp.call(parent, key))
            child[key] = parent[key];
    }

    function ctor() {
        this.constructor = child;
    }

    ctor.prototype = parent.prototype;

    child.prototype = new ctor();
    child.__super__ = parent.prototype;

    return child;
};

ContentTools.Tools.Subheading3 = (function(_super) {
    __extends(Subheading3, _super);

    function Subheading3() {
    return Subheading3.__super__.constructor.apply(this, arguments);
    }

    ContentTools.ToolShelf.stow(Subheading3, 'subheading3');
    Subheading3.label = 'Subheading3';
    Subheading3.icon = 'subheading3';
    Subheading3.tagName = 'h3';

    return Subheading3;

})(ContentTools.Tools.Heading);

ContentTools.Tools.Subheading4 = (function(_super) {
    __extends(Subheading4, _super);

    function Subheading4() {
    return Subheading4.__super__.constructor.apply(this, arguments);
    }

    ContentTools.ToolShelf.stow(Subheading4, 'subheading4');
    Subheading4.label = 'Subheading4';
    Subheading4.icon = 'subheading4';
    Subheading4.tagName = 'h4';

    return Subheading4;

})(ContentTools.Tools.Heading);

ContentTools.Tools.Subheading5 = (function(_super) {
    __extends(Subheading5, _super);

    function Subheading5() {
    return Subheading5.__super__.constructor.apply(this, arguments);
    }

    ContentTools.ToolShelf.stow(Subheading5, 'subheading5');
    Subheading5.label = 'Subheading5';
    Subheading5.icon = 'subheading5';
    Subheading5.tagName = 'h5';

    return Subheading5;

})(ContentTools.Tools.Heading);

/*
* Image uploader
*/
function imageUploader(dialog) {
    var image, xhr, xhrComplete, xhrProgress;

    // Image upload cancel
    dialog.addEventListener('imageuploader.cancelUpload', function () {
        // Stop the upload
        if (xhr) {
            xhr.upload.removeEventListener('progress', xhrProgress);
            xhr.removeEventListener('readystatechange', xhrComplete);
            xhr.abort();
        }

        // Set the dialog to empty
        dialog.state('empty');
    });

    // Image clear
    dialog.addEventListener('imageuploader.clear', function () {
        dialog.clear();
        image = null;
    });

    // Image upload
    dialog.addEventListener('imageuploader.fileready', function (ev) {

        // Upload a file to the server
        var formData;
        var file = ev.detail().file;

        // Define functions to handle upload progress and completion
        xhrProgress = function (ev) {
            // Set the progress for the upload
            dialog.progress((ev.loaded / ev.total) * 100);
        }

        xhrComplete = function (ev) {
            var response;

            // Check the request is complete
            if (ev.target.readyState != 4) {
                return;
            }

            // Clear the request
            xhr = null
            xhrProgress = null
            xhrComplete = null

            // Handle the result of the upload
            if (parseInt(ev.target.status) == 200) {
                // Unpack the response (from JSON)
                response = JSON.parse(ev.target.responseText);

                // Store the image details
                image = {
                        size: response.size,
                        alt: response.filename,
                        url: response.url
                        };

                // Populate the dialog
                dialog.populate(image.url, image.size);
            } else {
                // The request failed, notify the user
                new ContentTools.FlashUI('no');
            }
        }

        // Set the dialog state to uploading and reset the progress bar to 0
        dialog.state('uploading');
        dialog.progress(0);

        // Build the form data to post to the server
        formData = new FormData();
        formData.append('image', file);

        // Make the request
        xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', xhrProgress);
        xhr.addEventListener('readystatechange', xhrComplete);
        xhr.open('POST', siteUrl+'/samuell/contenteditor/image/upload', true);
        xhr.send(formData);
    });

    // Image save
    dialog.addEventListener('imageuploader.save', function () {
        var crop, cropRegion, formData;

        // Define a function to handle the request completion
        xhrComplete = function (ev) {
            // Check the request is complete
            if (ev.target.readyState !== 4) {
                return;
            }

            // Clear the request
            xhr = null
            xhrComplete = null

            // Free the dialog from its busy state
            dialog.busy(false);

            // Handle the result of the rotation
            if (parseInt(ev.target.status) === 200) {
                // Unpack the response (from JSON)
                var response = JSON.parse(ev.target.responseText);

                // Trigger the save event against the dialog with details of the
                // image to be inserted.
                dialog.save(
                    response.url,
                    response.size,
                    {
                        'alt': response.alt,
                        'data-ce-max-width': response.size[0],
                    });

            } else {
                // The request failed, notify the user
                new ContentTools.FlashUI('no');
            }
        }

        // Set the dialog to busy while the rotate is performed
        dialog.busy(true);

        // Build the form data to post to the server
        formData = new FormData();
        formData.append('url', image.url);
        formData.append('width', image.size[0]);
        formData.append('height', image.size[1]);
        formData.append('alt', image.alt);

        // Check if a crop region has been defined by the user
        if (dialog.cropRegion()) {
            formData.append('crop', dialog.cropRegion());
        }

        // Make the request
        xhr = new XMLHttpRequest();
        xhr.addEventListener('readystatechange', xhrComplete);
        xhr.open('POST', siteUrl+'/samuell/contenteditor/image/save', true);
        xhr.send(formData);
    });

}
ContentTools.IMAGE_UPLOADER = imageUploader;
