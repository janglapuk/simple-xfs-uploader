# Simple XFileSharing Uploader #
Based on `http://sibsoft.net/xfilesharing.html`, I create a simple PHP script for upload.

### System requirements: ###
  * Linux/UNIX O/S
  * cURL
  * PHP


### Usage: ###
  1. Get source code from repository
  1. Change permission on script file as an executable script:
```
chmod 755 xfs-uploader.php
```
  1. Edit `xfs-uploader.php` script and change some variable:
```
define('USERNAME', 'free'); // change with your registered username
define('PASSWORD', 'free'); // change with your registered password
define('CURL_STARTURL', 'http://www.some-xfs-file-hosting.com/');
```
  1. and the last step, execute script like:
```
./xfs-uploader.php /home/user/some/example-video-file.avi
```
> or you can use command like:
```
php xfs-uploader.php /home/user/some/example-video-file.avi
```


### Output (example): ###
> ![http://3.bp.blogspot.com/-rJ0M9iro30I/TpKhCuA5ahI/AAAAAAAAAFA/SntpjZUZH90/s1600/snappy_01.jpg](http://3.bp.blogspot.com/-rJ0M9iro30I/TpKhCuA5ahI/AAAAAAAAAFA/SntpjZUZH90/s1600/snappy_01.jpg)


### Info: ###
> Website that using XFileSharing:
  * `http://movreel.com`
  * `http://carrier.so`
  * `http://bzlink.us`
  * `http://www.vidhog.com`
  * `http://glumbouploads.com`
  * `http://www.asixfiles.com`
  * `http://www.maknyos.com`
  * or your can find yourself with Google dork: `"Show Advanced" "Recipient's Email:" "Link Password:"`

Enjoy!