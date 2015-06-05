# multiedit
Organize / write / view comparison note on multiple projects on web.

### Features
- No user authentication. global add/edit/del.
- Multiple projects can be documented sharing single category tree.
- Good for product comparison
- Scroll is synced in both projects.
- Edit/View mode

## Requirements
- PHP5+
- Apache2+
- Mysql5+

### Demo
- https://1kko.github.io/multiedit

## Compatibility.
- Chrome
- Firefox
- _Internet Explorer is ** not tested.**_ _(probably won't work)_

## Installation
  1. Extract all files to somewhere webroot.

  `git clone git@github.com:1kko/multiedit.git /var/www/html/`

  2. Create Database and Database user

  eg. id: `multiedit` / pw: `qwe123` / db: `multiedit`

  3. Use `multiedit.sql` to structure the Database.

  eg. `mysql -u your_user -p multiedit < multiedit.sql`
  
  4. change last line of `/php/common/db.php`, where it looks like:
  ```
  $db = new db("mysql:host=127.0.0.1;port=3306;dbname=multiedit", "multiedit", "qwe123");
  ```
  
  5. _(Optional) you might want to increase the value on following configurations in `php.ini`_

  `sudo gedit /etc/php5/apache2/php.ini`
  
  ```
  memory_limit 1024M
  post_max_size 1024M
  upload_max_filesize 1024M
  ```
  
  6. Access with webbrowser.
  eg. `http://localhost/multiedit/`


## I used following libraries and codes.
Icons, theme, editor, notifications, tree, lightbox, and many others are brought from following.,

- jQuery
  https://jquery.com

- Bootstrap
  http://getbootstrap.com

- Iconset
  http://axicon.axisj.com

- Editor
  http://www.tinymce.com

- Tinymce image upload plugin.
  https://github.com/vikdiesel/justboil.me

- jsTree
  http://www.jstree.com

- jsTree bootstrap theme
  https://github.com/orangehill/jstree-bootstrap-theme

- Select for bootstrap
  http://silviomoreto.github.io/bootstrap-select

- Switch for bootstrap
  https://github.com/nostalgiaz/bootstrap-switch

- Notification for bootstrap
  https://github.com/mouse0270/bootstrap-notify

- jQuery Cookie support
  https://github.com/carhartl/jquery-cookie

- Image Lightbox
  https://github.com/jackmoore/colorbox

- Search Highlight Gist
  https://gist.github.com/jonraasch/563055

## Contribution
Thanks to eisutal@gmail.com to creating backend
