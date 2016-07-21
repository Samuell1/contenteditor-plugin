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
* Load ContentTools
*/

var editor = ContentTools.EditorApp.get();
editor.init('*[data-editable]', 'data-file');

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
* Image uploader
*/
function imageUploader(dialog) {
    var image, xhr, xhrComplete, xhrProgress;

    // Image upload cancel
    dialog.addEventListener('imageuploader.cancelUpload', function () {

        if (xhr) {
            xhr.upload.removeEventListener('progress', xhrProgress);
            xhr.removeEventListener('readystatechange', xhrComplete);
            xhr.abort();
        }

        dialog.state('empty');
    });

    // Image clear
    dialog.addEventListener('imageuploader.clear', function () {
        dialog.clear();
        image = null;
    });

    // Image upload
    dialog.addEventListener('imageuploader.fileready', function (ev) {

        var formData;
        var file = ev.detail().file;

        dialog.state('uploading');
        dialog.progress(0);

        var formData = new FormData();
        formData.append('image', file);

        $.request("contenteditor::onUploadImage", {
            proccessData: false,
            contentType: false,
            data: formData,
            xhr: function() {

                var xhr = new window.XMLHttpRequest();

                xhr.upload.addEventListener("progress", function(evt) {
                  if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    dialog.progress(percentComplete);

                    if (percentComplete === 100) {
                      //Upload Is Complete
                    }
                  }
                }, false);
                return xhr;
            },
            success: function(result) {
                var response = JSON.parse(result);

                dialog.save(
                  response.url,
                  response.size,
                  {
                      'alt': response.alt,
                      'data-ce-max-width': response.size['width']
                  }
                );

            }
        });

    });

    // Image save
    dialog.addEventListener('imageuploader.save', function () {
        var crop, cropRegion, formData;

        dialog.busy(true);

        var formData = new FormData();
        formData.append('url', image.url);
        formData.append('width', 600);

        $.request("contenteditor::onSaveImage", {
            proccessData: false,
            contentType: false,
            data: formData,
            xhr: function() {

                var xhr = new window.XMLHttpRequest();

                xhr.upload.addEventListener("progress", function(evt) {
                  if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    console.log(percentComplete);
                    dialog.progress(percentComplete);

                    if (percentComplete === 100) {
                      //Upload Is Complete
                    }
                  }
                }, false);
                return xhr;
            },
            success: function(result) {
                var response = JSON.parse(result);

                dialog.save(
                  response.url,
                  response.size,
                  {
                      'alt': response.alt,
                      'data-ce-max-width': response.size['width']
                  }
                );

            }
        });

        xhr = null
        xhrComplete = null
    });

}
ContentTools.IMAGE_UPLOADER = imageUploader;
