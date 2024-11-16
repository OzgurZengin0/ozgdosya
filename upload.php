<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];


    $user_upload_dir = 'uploads/' . $user_id; 
    if (!is_dir($user_upload_dir)) {
        mkdir($user_upload_dir, 0777, true); 
    }

    $upload_path = $user_upload_dir . '/' . $file_name; 


    if (move_uploaded_file($file_tmp, $upload_path)) {
        $stmt = $pdo->prepare("INSERT INTO files (user_id, file_name, file_path) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $file_name, $upload_path]);
        $success_message = "Dosya başarıyla yüklendi!";
    } else {
        $error_message = "Dosya yüklenirken bir hata oluştu!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input[type="file"] {
            width: calc(100% - 20px);
            max-width: 360px;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background: #6a11cb;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            max-width: 360px;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #2575fc;
        }
        .message {
            margin-top: 15px;
            font-size: 14px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .btn-back {
            display: inline-block;
            margin-top: 15px;
            background: #ddd;
            color: #333;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background: #bbb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Dosya Yükle</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Yükle</button>
        </form>
        <?php if (!empty($success_message)): ?>
            <p class="message success"><?= htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p class="message error"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <a href="dashboard.php" class="btn-back">Panelime Dön</a>
    </div>
</body>
</html>
