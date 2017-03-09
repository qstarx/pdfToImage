<?php
include_once __DIR__.'/config/config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<!-- CSS -->

<link rel="stylesheet" href="include/css/styles.css" media="screen" />

<!-- JS -->

<script type="text/javascript" src="include/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="include/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="include/js/upload_pdf.js"></script>
<script type="text/javascript" src="include/js/handlebars-v4.0.5.js"></script>

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
          <div class"pdf-page-info"><button data-path="{{ this.img_path}}">übernehmen</button></div>
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