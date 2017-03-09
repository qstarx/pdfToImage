
var pdfuploader = function(opt) {

  var mp = {}
  if (window.session_id) mp["session_id"] = window.session_id

  var showStatus = function(stat) {

    console.log(stat)
  }

  var uploader = new plupload.Uploader({
    opt:opt,
    browse_button: 'pick_pdf', // you can pass in id...
    url: 'bin/save_pdf.php',
    runtimes: 'html5,flash,html4',
    container: 'upload-container', // ... or DOM Element itself
    chunk_size: '512kb',
    multipart_params: mp,
    // Flash settings
    flash_swf_url: '/plupload/js/Moxie.swf',

    // Preview url
    preview_url: 'bin/get_preview.php',

    // save url
    save_url: 'pdf/save_object.php',

    init: {

      PostInit: function (up) {
        if (opt.onInit) opt.onInit(up)
      },

      FilesAdded: function (up, files) {
        // start uploading - we only accept one file
        $('#pdf-import-result').html('')
        $('#pdf-preview').html('')
        uploader.start();
      },

      UploadProgress: function (up, file) {
        $('#progress span').css('width', file.percent + "%");
        if(file.percent>=100) $('#progress span').css('width', 0);
      },

      Error: function (up, err) {
        showStatus("Upload failed: " + err.code + ": " + err.message);
      },


      FileUploaded: function(up, file, response) {
        uploader.removeFile(file);
        this.uploadResponse = JSON.parse(response.response)
        if (opt.onUpload) opt.onUpload(up,file, this.uploadResponse, this)
      },
      UploadComplete: function(up, files) {

      }
    },

    get_preview: function (onLoad) {

      if (this.settings) that = this.settings
      else that = this

      var size = []; 
      if (uploader.uploadResponse && uploader.uploadResponse.pdfcheck) size = [uploader.uploadResponse.pdfcheck.pageinfo.pages[1].width, uploader.uploadResponse.pdfcheck.pageinfo.pages[1].height]
      $.post(
        that.preview_url,
        { filename: uploader.uploadResponse.filename, dir:uploader.uploadResponse.dir, size: size },
        function (data) {
          if (typeof onLoad == "function") onLoad(data)
        },
        "json"
      );
    },

    save_pdf: function () {

      if(!KID){
        document.location.href="/kalender_pdf-upload.php?schritt=fertigstellen&" + session_name + '=' + session_id;

      } else {
        if (this.settings) that = this.settings
        else that = this

        var dataObj
        if(typeof uploader.uploadResponse!='undefined'){
          dataObj = uploader.uploadResponse
        } else if(typeof previewResult!='undefined'){
          dataObj = previewResult
        }

        console.log("save " + dataObj.filename)

        var type = "";
        if (dataObj.pdfmatch.products[0]) type = dataObj.pdfmatch.products[0].objekttyp

        $.post(
          that.save_url,
          { filename: dataObj.filename, dir:dataObj.dir, objekttyp:type, modul:'kalender' },
          function (data) {
            if (typeof opt.onSave == "function") opt.onSave(data)
              },
          "json"
        );

      }


    }

  });



  uploader.init();
  uploader.save = uploader.settings.save_pdf
  uploader.get_preview = uploader.settings.get_preview

  return uploader

}

var previewResult = false;
window.onload = function () {

  
  pdfup = pdfuploader({
    onUpload: function(up, file, response, uploader) {

      var source   = $("#pdf-import-template").html();
      var template = Handlebars.compile(source);

      $('#pdf-import-result').html(template({ file:file,response:response }))

      $('#kalendereditor-button').hide();

      if(typeof uploader.uploadResponse.pdfmatch.error=='undefined'){

          $('#pdf-preview').html('<div class="center"><img src="img/ajax-loader.gif"/><h3>Lade Voransicht...</h3></div>')

          uploader.settings.get_preview(function(data) {
            var source   = $("#pdf-preview-template").html();
            var template = Handlebars.compile(source);

            $('#pdf-preview').html(template({ image:data.image }))
          })
       

      }

    },

    onSave:function(data) {
      if (data["error"]) {
        alert("Fehler beim Speichern");
      } else {
        if(KID){
          var href = "/in_auftrag.php?hidden_string=o" + data.JID;
          if (parent.window.opener) {
            parent.window.opener.location.href=href;
          } else if (parent.window) {
            parent.window.location.href=href;
          } else {
            document.location.href=href;
          }
        }
      }
    }
  });

  if(previewResult){

    var preview_url = 'bin/get_preview.php';

    $.post(
      preview_url,
      { filename: previewResult.filename, dir: previewResult.dir, size: [previewResult.pdfcheck.pageinfo.pages[1].width, previewResult.pdfcheck.pageinfo.pages[1].height] },
      function (data) {
        var source   = $("#pdf-import-template").html();
        var template = Handlebars.compile(source);
        $('#pdf-import-result').html(template({ file: { name: previewResult.filename }, response: previewResult }))

        if(typeof previewResult.pdfmatch.error=='undefined'){

          $('#step-upload-content').slideUp(0, function(){

            $('#pdf-preview').html('<div class="center"><img src="/bilder/ajax-loader.gif"/><h3>Lade Voransicht...</h3></div>')

            var source   = $("#pdf-preview-template").html();
            var template = Handlebars.compile(source);
            $('#pdf-preview').html(template({ image: data.image }))
          });
        }
      },
      "json"
    );
  }
}