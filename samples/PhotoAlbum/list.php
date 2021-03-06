<?php require 'main.php' ?>
<html>
  <head>
    <meta charset="utf-8">
    <title>PhotoAlbum - Main page</title>

	<link href="style.css" media="all" rel="stylesheet" />

    <link rel="shortcut icon"
     href="<?php echo cloudinary_url("http://cloudinary.com/favicon.png",
           array("type" => "fetch")); ?>" />
           
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

    <script type='text/javascript'>
      $(function () {
        $('.toggle_info').click(function () {
          $(this).closest('.photo').toggleClass('show_more_info');
          return false;
        });
      });
    </script>
  </head>
  <body>
  	
    <div id="logo">
        <!-- This will render the image fetched from a remote HTTP URL using Cloudinary -->
        <?php echo fetch_image_tag("http://cloudinary.com/images/logo.png") ?>
    </div>
    
    <div id="posterframe-list">
        <!-- This will render the fetched Facebook profile picture using Cloudinary according to the
             requested transformations.
             I took the freedom to remove the thumbnail of the almighty Chuck Norris and replace it
             with a tweaked version of my facebook profile pic :) -->
        <?php echo facebook_profile_image_tag("1062577706", array(
            "format" => "png",
            "transformation" => array(
                array("height" => 150, "width" => 150, "crop" => "thumb", "gravity" => "face",
                    "effect" => "improve", "radius" => "max"), array(
                        "overlay" => "fetch:http://res.cloudinary.com/raziv/image/upload/monoklo.png",
                            "height" => 75, "width" => 90, "angle" => 15, "x" => 5, "y"=> 5), array(
                                "overlay" => "fetch:http://res.cloudinary.com/raziv/image/upload/black-hat.png",
                                    "width" => 130, "crop" => "limit", "angle" => 15, "x" => "3", "y" => "-60"
            ))));
        ?>
    </div>
    
    <h1>Welcome!</h1>
    
    <p>
    	This is the main demo page of the PhotoAlbum sample PHP application of Cloudinary.<br />
    	Here you can see all images you have uploaded to this PHP application and find some information on how
    	to implement your own PHP application storing, manipulating and serving your photos using Cloudinary!
    </p>

    <p>
    	All of the images you see here are transformed and served by Cloudinary. 
    	For instance, the logo and the poster frame. 
    	They are both generated in the cloud using the Cloudinary shortcut functions: fetch_image_tag and facebook_profile_image_tag. 
    	These two pictures weren't even have to be uploaded to Cloudinary, they are retrieved by the service, transformed, cached and distributed through a CDN.
    </p>

    <h1>Your Images</h1>
    <div class="photos">
	  <p>
	  	Following are the images uploaded by you. You can also upload more pictures.
	    
	    You can click on each picture to view its original size, and see more info about and additional transformations.
	    <a class="upload_link" href="upload.php">Upload Images...</a>
	  </p>
      <?php if (R::count('photo') == 0) { ?>
        <p>No images were uploaded yet.</p>
      <?php
        }
        $index = 0;
        foreach (R::findAll('photo') as $photo) {
      ?>
        <div class="photo">
            <a href="<?php echo cloudinary_url($photo["public_id"], 
                array("format" => $photo["format"])) ?>" target="_blank" class="public_id_link">
                <?php 
                  echo "<div class='public_id'>" . $photo["public_id"] . "</div>";
                  echo cl_image_tag($photo["public_id"], array_merge($thumbs_params, array("crop" => "fill")));
                ?>
            </a>
          
          <div class="less_info">
            <a href="#" class="toggle_info">More transformations...</a>
          </div>
          
          <div class="more_info">
            <a href="#" class="toggle_info">Hide transformations...</a>
            <table class="thumbnails">
              <?php
                /**
                 * Added two additional transformations to the already existitng ones:
                 *
                 * 1. The most left thumbnail includes Cloudinary watermark as an overlay appended to the bottom right
                 * side of the photo.
                 * 2. The thumbnail next to the right is a 50% increased saturation version of the original photo
                 *    uploaded.
                 */
                $thumbs = array(
                  array("override" => true, "transformation" => array(
                      array("crop" => "thumb", "height" => 150, "width" => 150), array(
                              "overlay" => "fetch:https://res.cloudinary.com/demo/image/upload/cloudinary_icon.png",
                                   "gravity" => "south_east", "width" => "55", "crop" => "limit"
                      ))),
                  array("crop" => "thumb", "effect" => "saturation:50"),
                  array("crop" => "fill", "radius" => 10),
                  array("crop" => "scale"),
                  array("crop" => "fit", "format" => "png"),
                  array("crop" => "thumb", "gravity" => "face"),
                  array("override" => true, "format" => "png", "angle" => 20, "transformation" => 
                    array("crop" => "fill", "gravity" => "north", "width" => 150, "height" => 150, "effect" => "sepia")
                  ),
                );
                foreach($thumbs as $params) {
                  $merged_params = array_merge((\Cloudinary::option_consume($params, "override")) ? array() : $thumbs_params, $params);
                  echo "<td>";
				  echo "<div class='thumbnail_holder'>";
                  echo "<a target='_blank' href='" . cloudinary_url($photo["public_id"], $merged_params) . "'>" . 
                  	cl_image_tag($photo["public_id"], $merged_params) . "</a>";
				  echo "</div>";
                  echo "<br/>";
                  \PhotoAlbum\array_to_table($merged_params);
                  echo "</td>";
                }
              ?>
              
            </table>
            
            <div class="note">             	
            	Take a look at our documentation of <a href="http://cloudinary.com/documentation/image_transformations" target="_blank">Image Transformations</a> for a full list of supported transformations.
            </div>	
          </div>
        </div>
      <?php $index++; } ?>
    </div>
  </body>
</html>
