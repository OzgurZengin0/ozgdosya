http://takipzirvesi.tr.ht/<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND id = ?");
    $stmt->execute([$user_id, $delete_id]);
    $file = $stmt->fetch();

    if ($file) {
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }

        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$delete_id]);


        header("Location: dashboard.php"); 
        exit;
    }
}


$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

// Sayfalama ayarları
$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$total_files = count($files); 
$total_pages = ceil($total_files / $limit); 
$offset = ($page - 1) * $limit; 

// Sayfalama için dosyaları al
$files_to_display = array_slice($files, $offset, $limit);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panelim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 800px;
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .files {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
            margin: 20px 0;
        }
        .file-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            width: 220px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .file-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .file-icon {
            font-size: 50px;
            color: #6a11cb;
            margin-bottom: 10px;
        }
        .file-name {
            font-size: 14px;
            color: #333;
            margin: 10px 0;
            font-weight: bold;
        }
        .btn-download {
            display: inline-block;
            background: #6a11cb;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn-download:hover {
            background: #2575fc;
        }
        .btn-upload {
            display: inline-block;
            background: #6a11cb;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn-upload:hover {
            background: #2575fc;
        }
        .btn-delete {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            background: #6a11cb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a:hover {
            background: #2575fc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Dosyalarım</h2>
        <?php if (!empty($files_to_display)): ?>
            <div class="files">
                <?php foreach ($files_to_display as $file): ?>
                    <div class="file-card">
                        <div class="file-icon">📄</div>
                        <p class="file-name"><?= htmlspecialchars($file['file_name']); ?></p>
                        <a href="<?= htmlspecialchars($file['file_path']); ?>" class="btn-download" download>İndir</a>
                        <a href="?delete_id=<?= $file['id']; ?>" class="btn-delete" onclick="return confirm('Bu dosyayı silmek istediğinize emin misiniz?');">Sil</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Henüz herhangi bir dosya yüklemediniz.</p>
        <?php endif; ?>
        <a href="upload.php" class="btn-upload">Dosya Yükle</a>

        <!-- Sayfalama Linkleri -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1">İlk</a>
                <a href="?page=<?= $page - 1; ?>">Önceki</a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1; ?>">Sonraki</a>
                <a href="?page=<?= $total_pages; ?>">Son</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
