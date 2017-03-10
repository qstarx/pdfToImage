# pdfToImage

Web service for creating Jpeg Images from PDF Pages (hires/preview/thumb). This code was developed for online printing services.

## Demo

   See in production on:
   - PDF Format checking [kalender-druck.de](https://www.kalender-druck.de) ( -> PDF hochladen )
   - Conversion of Pages [fototasse.com](https://www.fototasse.com/fototassen-hilfe.php) ( -> PDF Import )

## Dependencies

  - webserver running php
  - convert (imagemagick) 
  - nconvert (XnView) for fast rescaling
  - beanstalkd for background processing [Optional]
  - pdfcheck [Optional] License required. see [pstill.com](http://www.pstill.com)

## Installation

  - Install with composer
  - edit `config/config.php` to your needs
  - create upload dir and change owner to webserver user

```
    mkdir tmp
    mkdir tmp/uploads
    chown -R www-data tmp
    chmod -R u+rX tmp
```
  - run worker daemon (Use supervisord for stability):
    
    `php bin/worker/worker.php`
    
## Options

  - Activate useJobQueue in `config/config.php` for background processing. Using job queue is recomended for large PDFs. 
This sends the rendering process to background using beanstalkd. Requires start of bin/worker/worker.php in CLI mode

  - Activate pdfcheck in `config/config.php` to get a fast analysis of the file. It requires an installation of Frank Siegert's pdfcheck.
This tool performs various checks including fonts, colors, bounding boxes, embedded images. The results are returned after upload has completed.