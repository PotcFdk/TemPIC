<?php
include_once('config.php');

function isImage($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file);
    finfo_close($finfo);

    return ($mime == 'image/gif')
        || ($mime == 'image/jpeg')
        || ($mime == 'image/jpg')
        || ($mime == 'image/pjpeg')
        || ($mime == 'image/x-png')
        || ($mime == 'image/png');
}

function rearrange($arr) {
    foreach ($arr as $key => $all) {
        foreach($all as $i => $val) {
            $new[$i][$key] = $val;    
        }    
    }
    return $new;
}

if (is_uploaded_file($_FILES['file']['tmp_name'][0])) {
    session_start();
    $files = array();

    // Because PHP structures the array in a retarded format
    $_FILES['file'] = rearrange($_FILES['file']);

    foreach ($_FILES['file'] as $file) {
        $files[$file['name']] = array();

        if ($file['size'] < 2000000) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

            if (in_array($extension, $DISALLOWED_EXTS)) {
                $files[$file['name']]['error'] = 'Disallowed file type!';
            } elseif ($file['error'] > 0) {
                $files[$file['name']]['error'] = 'Return Code: ' . $file['error'];
            } else {
                if (file_exists('upload/' . $file['name'])) {
                    $files[$file['name']]['error'] = $file['name'] . ' already exists.';
                } else {
                    $name = time() . '_' . rand(100000000, 999999999) . '_' . $file['name'];
                    $path = $PATH_UPLOAD . '/' . $name;
                    move_uploaded_file($file['tmp_name'], $path);

                    $link = $URL_BASE . '/' . $PATH_UPLOAD . '/' . $name;

                    $files[$file['name']]['link'] = $link;
                    $files[$file['name']]['image'] = isImage($path);
                    $files[$file['name']]['extension'] = $extension;
                }
            }
        } else {
            $files[$file['name']]['error'] = 'File too large!';
        }
    }

    $_SESSION['files'] = $files;
}

header('Location: ' . $URL_BASE);