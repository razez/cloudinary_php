<?php
namespace PhotoAlbum {
  require 'lib/rb.php';
  require '../../src/Cloudinary.php';
  require '../../src/Uploader.php';
  require '../../src/Api.php'; // Only required for creating upload presets on the fly
  error_reporting(E_ALL | E_STRICT);

  // Sets up Cloudinary's parameters and RB's DB
  include 'settings.php';

  // Global settings
  if (array_key_exists('REQUEST_SCHEME', $_SERVER)) {
    $cors_location = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] .
        dirname($_SERVER["SCRIPT_NAME"]) . "/lib/cloudinary_cors.html";
  } else {
    $cors_location = "http://" . $_SERVER["HTTP_HOST"] . "/lib/cloudinary_cors.html";
  }

  $thumbs_params = array("format" => "jpg", "height" => 150, "width" => 150,
    "class" => "thumbnail inline");

  /**
   * Global options to send on upload (in both backend and direct uploads), according to the assignment specifications:
   *   1. Add a tag. In this case, just appending a random digit between 1-9 to the "tag" prefix.
   *   2. Automatically re-size images bigger than 500x500 pixels in dimensions, while retaining original aspect ratio.
   *
   *   Additional notes: For unsinged direct uploads, options like height, width and crop are prohibited.
   *                     Therefore I had to add them to the preset created on first unsinged upload.
   *                     By default the create_upload_preset function in \Cloudinary\Api class allow only the following
   *                     options to be added: "name", "unsigned", "disallow_public_id". I had to modify it to also
   *                     include height, width and crop. Though, instead of changing a core function behavior,
   *                     this might also be achieved by changing the code of the jQuery direct upload plug-in. I assumed
   *                     it was out of the scope of this assignment.
   *                     Additionally, it's possible to create/edit the preset used in the code, using the Console
   *                     Management web interface, and include the transformations.
   */
  $backend_upload_options = array(
      'tags' => "tag".rand(1,9),
      'height' => 500,
      'width' => 500,
      'crop' => "limit"
  );

  $direct_upload_options = array(
      'tags' => "tag".rand(1,9),
      'height' => 500,
      'width' => 500,
      'crop' => "limit",
      'callback' => $cors_location,
      'html' => array(
          'multiple' => true
    ));

  // Helper functions
  function ret_var_dump($var) {
    ob_start();
    var_dump($var);
    return ob_get_clean();
  }

  function array_to_table($array) {
    $saved_error_reporting = error_reporting(0);
    echo "<table class='info'>";
    foreach ($array as $key => $value) {
      if ($key != 'class') {
        if ($key == 'url' || $key == 'secure_url') {
          $display_value = '"' . $value . '"';
        } else {
          $display_value = json_encode($value);
        }
        /* Long transformation strings were messing the gallery view, pushing the other thumbnails too much to the right.
           Therefore I've added chunk_split to force a new line every 20 chars.*/
        echo "<tr><td>" . $key . ":</td><td>" . chunk_split($display_value, 20) . "</td></tr>";
      }
    }
    echo "</table>";
    error_reporting($saved_error_reporting);
  }

  function create_photo_model($options = array()) {
    $photo = \R::dispense('photo');

    foreach ( $options as $key => $value ) {
      if ($key != 'tags') {
        $photo->{$key} = $value;
      }
    }

    # Add metadata we want to keep:
    $photo->moderated = false;
    $photo->created_at = (array_key_exists('created_at', $photo) ?
      DateTime::createFromFormat(DateTime::ISO8601, $photo->created_at) :
      \R::isoDateTime());

    $id = \R::store($photo);
  }
}
