<?php
$VERSION = "1.0.0";

#$DEBUG = isset($_ENV['DEBUG_APP']) ? $_ENV['DEBUG_APP'] : true;
$DEBUG = true;
debug($DEBUG);

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>

<h1>Version <?php echo $VERSION; ?></h1>
<div style="display:<?php echo $DEBUG ? 'block': 'none'; ?>;">
    <div class="container-fluid">
        <div class="alert alert-warning" role="alert">
        <?php
        $db = connectDB();
        createTable($db);
        $method = $_SERVER['REQUEST_METHOD'];
        if ( 'POST' === $method && $_REQUEST['title'] != '' && $_FILES["fileToUpload"]["name"] != '') :
            $title = $_REQUEST['title'];
            $timestamp = round(microtime(true) * 1000);
            $target_dir = "uploads/";
            $target_file = $target_dir . $timestamp . '_' . basename($_FILES["fileToUpload"]["name"]);
            if ( uploadFile($target_file) ) :
                if ( ! savePost($db, $title, $target_file) ) {
                    display("cannot save the post!!!");
                }
            endif;
        else: 
        ?>
        <?php endif; ?>
        <?php $allData = selectPosts($db); ?>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="alert alert-info" role="alert">
        <div>
            <h4>New Post</h4> 
        </div>
        <form action="/" method="POST" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="title" class="col-sm-2 col-form-label">Title</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" name="title" id="title">
                </div>
            </div>
            <div class="form-group row">
                <label for="fileToUpload" class="col-sm-2 col-form-label">Image</label>
                <div class="col-sm-10">
                <input type="file" class="form-control-plaintext" name="fileToUpload" id="fileToUpload">
                </div>
            </div>
            <div>
                <input type="submit" class="btn btn-primary"  value="Publish" name="submit">
            </div>
        </form>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
    <?php foreach($allData as $data) : ?>
    <div class="col">
        <div class="card" style="width: 12rem;">
            <img class="card-img-top" src="<?php echo $data->image; ?>" alt="Card image cap">
            <div class="card-body">
                <p class="card-text"><?php echo $data->title;?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
</body>
</html>

<?php

function debug($enable){
    if ( $enable ) {
        error_reporting(E_ALL);
        ini_set("display_errors", "On");
    }
}

function display($msg) {
    echo '<div>' . $msg . '</div>';
}

function connectDB(){

    $servername = "mysql";
    $username   = "blog";
    $dbname     = "blog";
    $password   = "ihIUh#iu&ytg23@#";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
        echo "<br>" . $e->getMessage();
    }
    return $conn;
}

function createTable($conn) {
    try {
        $sql = "CREATE TABLE posts (
        id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(256) NOT NULL,
        image VARCHAR(256) NOT NULL
        )";
        $conn->exec($sql);
        return true;
    }
    catch (PDOException $e){
        display( $sql . "<br>" . $e->getMessage() );
        return false;
    }
}

function savePost($conn, $title, $target_file){
    try {
        $sql = "INSERT INTO posts (title, image)
        VALUES ('$title', '$target_file')";
        $conn->exec($sql);
        return true;
    }
    catch (PDOException $e){
        display( $sql . "<br>" . $e->getMessage() );
        return false;
    }
}

function selectPosts($conn) {
    
    try {
        $stmt = $conn->prepare("SELECT * FROM posts ORDER BY posts.id DESC");
        $stmt->execute();
        $result = $stmt->setFetchMode(PDO::FETCH_OBJ);
        return $stmt->fetchAll();
    }
    catch (PDOException $e){
        display( $sql . "<br>" . $e->getMessage() );
        return null;
    }
}

function uploadFile($target_file) {

    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        display( "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            display("The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.");
            return true;
        } else {
            display("Sorry, there was an error uploading your file.");
        }
    }
    return false;;
}

?>
