#!/bin/bash

directory="$1"
zipfile="$2"
theme_name="$3"
childtheme="$4"

cp -R "wordpress" "$directory"
unzip "$zipfile" -d "$directory/wp-content/themes"
mkdir "$directory/wp-content/themes/$childtheme"
touch "$directory/wp-content/themes/$childtheme/functions.php"
cp "$directory/wp-content/themes/$theme_name/header.php" "$directory/wp-content/themes/$childtheme/header.php"
cp "$directory/wp-content/themes/$theme_name/footer.php" "$directory/wp-content/themes/$childtheme/footer.php"
cp "$directory/wp-content/themes/$theme_name/index.php" "$directory/wp-content/themes/$childtheme/index.php"