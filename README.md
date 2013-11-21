Stanford University
Moodle course format installation guide
=============

1. Download the Stanford Course Format, unzip it if needed.

2. Move it to the `moodle/course/format/` directory, naming it `stanford`

2. Copy `moodle/course/view.php` to `moodle/course/view.php-original` in order to make a backup of it.

3. Copy `moodle/course/format/stanford/view.php-example` to `moodle/course/view.php`, replacing the original 
   version of the file with the one from the `stanford/` directory.

4. Login into your Moodle site with an admin role. You should be prompted by the "Plugins check" screen to upgrade
   your database to install the plugin. Do so.

5. You can now create a new course with the Stanford Format:
    1. Click Turn editing on the top right.
    2. Click Courses under Site Administration on the left menu
    3. Click "add/edit courses"
    4. Click "Add new Course" and populate the basic course data
    5. Choose "Stanford Format" from the Course format drop-down menu
    6. Click "Save changes"

Stanford University
