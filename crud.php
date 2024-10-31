<?php
// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "berita";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi variabel
$id = $judul = $isi = $kategori = $author = $tanggal_publikasi = $images = '';
$success_message = $error_message = '';

// Handle form submission (Create & Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $isi = $_POST['isi'];
    $kategori = $_POST['kategori'];
    $author = $_POST['author'];
    $tanggal_publikasi = $_POST['tanggal_publikasi'];
    $images = '';

    // Mengupload gambar
    if (isset($_FILES['images']) && $_FILES['images']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["images"]["name"]);
        
        // Cek apakah file gambar adalah gambar
        $check = getimagesize($_FILES["images"]["tmp_name"]);
        if ($check === false) {
            $error_message = "File yang diupload bukan gambar.";
        } else {
            // Cek ukuran file (misalnya maksimum 2MB)
            if ($_FILES["images"]["size"] > 2000000) {
                $error_message = "Ukuran file terlalu besar.";
            } else {
                // Pindahkan file ke folder tujuan
                if (move_uploaded_file($_FILES["images"]["tmp_name"], $target_file)) {
                    $images = basename($_FILES["images"]["name"]); // Simpan nama file gambar
                } else {
                    $error_message = "Gagal mengupload file.";
                }
            }
        }
    } else {
        if ($_FILES['images']['error'] != 4) { // Cek jika ada kesalahan lain
            $error_message = "Gagal mengupload file: " . $_FILES['images']['error'];
        }
    }

    if (empty($error_message)) {
        if (isset($_POST['create'])) {
            $stmt = $conn->prepare("INSERT INTO posts (judul, isi, kategori, author, tanggal_publikasi, images) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $judul, $isi, $kategori, $author, $tanggal_publikasi, $images);

            if ($stmt->execute()) {
                $success_message = "Post berhasil ditambahkan!";
            } else {
                $error_message = "Gagal menambahkan post: " . $stmt->error;
            }
            $stmt->close();
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("UPDATE posts SET judul=?, isi=?, kategori=?, author=?, tanggal_publikasi=?, images=? WHERE id=?");
            $stmt->bind_param('ssssssi', $judul, $isi, $kategori, $author, $tanggal_publikasi, $images, $id);

            if ($stmt->execute()) {
                $success_message = "Post berhasil diupdate!";
            } else {
                $error_message = "Gagal mengupdate post: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle edit action
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM posts WHERE id=$id");

    if ($result && $post = $result->fetch_assoc()) {
        $judul = $post['judul'];
        $isi = $post['isi'];
        $kategori = $post['kategori'];
        $author = $post['author'];
        $tanggal_publikasi = $post['tanggal_publikasi'];
        $images = $post['images'];
    } else {
        $error_message = "Post tidak ditemukan.";
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM posts WHERE id=$id")) {
        $success_message = "Post berhasil dihapus!";
    } else {
        $error_message = "Gagal menghapus post: " . $conn->error;
    }
}

// Baca semua post
$result = $conn->query("SELECT * FROM posts");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CRUD Blog</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .notification {
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, select, textarea, button {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            width: 50px;
            height: auto;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #007bff;
        }
        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Blog Posts</h1>

    <?php if ($success_message): ?>
        <div class="notification success"><?php echo $success_message; ?></div>
    <?php elseif ($error_message): ?>
        <div class="notification error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label>Judul</label>
        <input type="text" name="judul" value="<?php echo $judul; ?>" required>

        <label>Isi</label>
        <textarea name="isi" required><?php echo $isi; ?></textarea>

        <label>Kategori</label>
        <select name="kategori">
            <option value="Technology" <?php echo ($kategori == 'Technology') ? 'selected' : ''; ?>>Technology</option>
            <option value="Lifestyle" <?php echo ($kategori == 'Lifestyle') ? 'selected' : ''; ?>>Lifestyle</option>
        </select>

        <label>Author</label>
        <input type="text" name="author" value="<?php echo $author; ?>" required>

        <label>Tanggal Publikasi</label>
        <input type="date" name="tanggal_publikasi" value="<?php echo $tanggal_publikasi; ?>" required>

        <label>Gambar</label>
        <input type="file" name="images" accept="image/*" required>

        <button type="submit" name="<?php echo $id ? 'update' : 'create'; ?>">
            <?php echo $id ? 'Update' : 'Create'; ?>
        </button>
    </form>
        
    <table>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Isi</th>
            <th>Kategori</th>
            <th>Author</th>
            <th>Tanggal Publikasi</th>
            <th>Gambar</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['judul']; ?></td>
                <td><?php echo $row['isi']; ?></td>
                <td><?php echo $row['kategori']; ?></td>
                <td><?php echo $row['author']; ?></td>
                <td><?php echo $row['tanggal_publikasi']; ?></td>
                <td><img src="uploads/<?php echo $row['images']; ?>" alt="Image"></td>
                <td class="actions">
                    <a href="?edit=<?php echo $row['id']; ?>">Edit</a>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>
