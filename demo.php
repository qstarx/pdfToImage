<?php
include_once __DIR__.'/config/config.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"><head>
<!-- CSS -->

<link rel="stylesheet" href="include/css/styles.css" media="screen" />

<!-- JS -->

<script type="text/javascript" src="include/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="include/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="include/js/upload_pdf.js"></script>
<script type="text/javascript" src="include/js/handlebars-v4.0.5.js"></script>

<script>
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
    },
    
    onPageSave:function(data) {
      parent.document.location.href="/index.php";
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
</script>
</head>
<body>

<div id="upload-container">
   <a class="pd-button-color" id="pick_pdf"><span>PDF-Datei hochladen</span></a>
   <div id="pdf-import-result"></div>
   <div id="pdf-preview"></div>
</div>
<script id="pdf-preview-template" type="text/x-handlebars-template">
      <div class="pdf-preview">
      {{#each image}}
        <div class="pdf-page">
          <div><img src="{{ this.src}}"/></div>
          <div class"pdf-page-info"><button data-path="{{ this.img_path}}" onclick="pdfup.settings.save_page(this)">übernehmen</button></div>
        </div>
      {{/each}}
      </div>
</script>

<script id="pdf-import-template" type="text/x-handlebars-template">

  {{#if response.pdfcheck.pageinfo.pagecount}}
    <div class="panel big-margin-top margin fileinfo">
      <div><span>Dateiname: </span>{{ file.name }}</div>
      <div><span>Seitengröße: </span>{{ response.pdfcheck.pageinfo.width }} x {{ response.pdfcheck.pageinfo.height }} mm</div>
      <div><span>Seitenzahl: </span>{{ response.pdfcheck.pageinfo.pagecount }}</div>
    </div>
  {{/if}}

  {{#if response.pdfmatch.error.length}}
    <div class="margin">
      <div class="panel-border margin"><b class="red">PDF kann nicht importiert werden</b>
        <ul class="no-margin pdf-error">
        {{#each response.pdfmatch.error}}
          <li>{{ this.txt }}
          {{#if this.imgError}}
            [ <a href="" onclick="$(this).closest('li').find('ul').toggle(); return false;">Details</a> ]
            <ul style="display:none">
            {{#each this.imgError}}
              <li>Seite {{ this.onPage}}: Bild mit Auflösung von {{ this.DpiX}} x {{ this.DpiY}} dpi</li>
            {{/each}}
            </ul>
          {{/if}}
            {{#if this.fontError}}
            [ <a href="" onclick="$(this).closest('li').find('ul').toggle(); return false;">Details</a> ]
            <ul style="display:none">
            {{#each this.fontError}}
              <li>Font {{  this.name}} vom Typ {{ this.Type}} ist nicht eingebettet</li>
            {{/each}}
            </ul>
          {{/if}}
          </li>
        {{/each}}
        </ul>
      </div>
    </div>
  {{/if}}

  {{#if response.pdfmatch.products.length}}
    {{#unless response.pdfmatch.error.length}}
      <div class="margin">
        <div class="panel-border check margin"><b class="green">Das PDF wurde erfolgreich importiert</b><br>
          Das PDF passt zu folgenden Produkten:
          <ul class="no-margin pdf-match">
          {{#each response.pdfmatch.products}}
            <li>{{ this.artikel }}&nbsp;{{ this.format }}</li>
          {{/each}}
          </ul>
        </div>

        <div class="big-margin-top big-margin center">
          <a class="pd-button-color" href="#" onclick="pdfup.save()"><span>PDF für Bestellung übernehmen</span></a>
        </div>

      </div>
    {{/unless}}
  {{/if}}

</script>


</body>
</html>