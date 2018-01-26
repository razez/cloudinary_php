<?php
require 'main.php';

function create_photo( $file_path, $orig_name, $options )
{
    # Remove file type extension from file name, before using it as public_id.
    # Otherwise uploaded photo url looks like http://res.cloudinary.com/.../xxxxx.jpg.jpg
    $orig_name = substr($orig_name, 0, strlen($orig_name) - strlen(strrchr($orig_name, '.')));
    $options['public_id'] = $orig_name; // Add public_id to the global upload_options array

    # Upload the received image file to Cloudinary
    $result = \Cloudinary\Uploader::upload($file_path, $options);

    unlink($file_path);
    error_log("Upload result: " . \PhotoAlbum\ret_var_dump($result));
    $photo = \PhotoAlbum\create_photo_model($result);
    return $result;
}

$files = $_FILES["files"];
$files = is_array($files) ? $files : array( $files );
$files_data = array();
foreach ($files["tmp_name"] as $index => $value) {
    array_push($files_data, create_photo($value, $files["name"][$index], $backend_upload_options));
}

?>
<html>
<head>
    <link href="style.css" media="all" rel="stylesheet"/>
    <title>Upload succeeded!</title>
</head>
<body>

<h1>Your photo has been uploaded sucessfully!</h1>
<h2>Upload details:</h2>
<?php
foreach ($files_data as $file_data) {
    \PhotoAlbum\array_to_table($file_data);

    /* Inserted the printing of the photo uploaded into the foreach loop.
       Beforehand, when uploaded several photos at once, only the last uploaded photo was printed to the screen*/
    echo cl_image_tag($file_data['public_id'], array_merge($thumbs_params, array( "crop" => "fill" )));
}
?>
<br/>

<a href="list.php" class="back_link">Back to list...</a>

</body>
</html>
