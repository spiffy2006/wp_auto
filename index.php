<!DOCTYPE html>
<html>
    <head>
        <style type="text/css">
            form {
                width: 330px;
                margin: 0 auto;
            }            
            
            input[type="submit"] {
                border-radius: 600px;
                padding: 25px;
                height: 300px;
                width: 300px;
                border: 8px solid #FFF;
                font-size: 24px;
                color: #fff;
                background: #f00;
                box-shadow: 0 0 6px #000;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
<?php

if (isset($_POST['submit'])) :
    if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "<br>";
    } else {    
        $dirs = array_filter(glob('*'), 'is_dir');
        $dirs = array_values($dirs);
        $wfs = array();
        foreach($dirs as $dir) {
            if ( substr( $dir, 0, 2 ) == 'wf' ) {
                array_push($wfs, $dir);
            }
        }
        
        //Set all variables
        $db_name = 'wf' . (count($wfs) + 2); //+2 to account for theme-test which isn't normal naming convention
        $db_user = 'root';
        $db_pass = '';
        $site_title = 'SiteTitle';
        $user_name = 'user';
        $admin_pass = 'password';
        $email = 'email@email.com';
        
        //Copy wordpress files to new wireframe folder & unzip theme and place it in themes folder
        $theme_name = substr($_FILES["file"]["name"], 0, -4);
        $child_theme = $theme_name . "-Child";
        $zipFile = $_FILES["file"]["name"];
        move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);
        $command = escapeshellcmd("sh ./createwireframe.sh $db_name $zipFile $theme_name $child_theme");
        exec($command, $op, $rv);
        unlink($zipFile);
        //Create stylesheet
        $css = '/*
 Theme Name:   ' . $theme_name . ' Child
 Description:  ' . $theme_name . ' Child Theme
 Template:     ' . $theme_name . '
*/


@import url("../' . $theme_name . '/style.css");


/* =Theme customization starts here
-------------------------------------------------------------- */';
        file_put_contents($db_name . '/wp-content/themes/' . $child_theme . '/style.css', $css);
        
        try {
            $dbh = new PDO("mysql:host=localhost", $db_user, $db_pass);

            $dbh->exec("CREATE DATABASE `$db_name`;
                    GRANT ALL PRIVILEGES ON `$db_name`.* TO '$db_user'@'localhost';
                    FLUSH PRIVILEGES;") 
            or die(print_r($dbh->errorInfo(), true));

        } catch (PDOException $e) {
            die("DB ERROR: ". $e->getMessage());
        }
        
        //Create Config File & Connect to Database
        exec("curl --data 'dbname=$db_name&uname=$db_user&pwd=$db_pass&dbhost=localhost&prefix=wp_' http://wf.stageops.com/$db_name/wp-admin/setup-config.php?step=2");
        
        //Finish Site Set Up
        exec("curl --data 'weblog_title=$site_title&user_name=$user_name&admin_password=$admin_pass&admin_password2=$admin_pass&admin_email=$email&blog_public=0' http://wf.stageops.com/$db_name/wp-admin/install.php?step=2");
        echo '<a href="http://wf.stageops.com/' . $db_name . '/wp-admin" target="_blank">Login to new wireframe</a>';
    }
else: ?>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="file">Filename:</label>
            <input type="file" name="file" id="file"><br>
            <input name="submit" value="Create New Wireframe" type="submit" />
        </form>
<?php
endif;
?>
    </body>
</html>