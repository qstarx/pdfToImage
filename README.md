# pdfToImage

Webservice for creating Jpeg Images from PDF Pages (hires/preview/thumb). This code was developed for online printing services.

## Demo

   See in production on [kalender-druck.de](https://www.kalender-druck.de) ( -> PDF hochladen)

## Dependencies

  - convert (imagemagick)
  - nconvert (XnView) for fast rescaling
  - beanstalkd for background processing

## Installation

  - edit `config/config.php` to your needs
  - create upload dir and change owner to webserver user

```
    mkdir tmp
    mkdir tmp/uploads
    chown -R www-data tmp
```
  - run worker daemon (Use supervisord for stability):
    
    `php bin/worker/worker.php`
    