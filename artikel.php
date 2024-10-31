<?php
// Database connection parameters
$host = "localhost"; // Change if needed
$user = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$database = "berita"; // Name of your database

// Create a connection
$conn = new mysqli($host, $user, $password, $database);
require_once 'functions.php'; // Use require_once to prevent redeclaration
// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch specific post based on ID
if (isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
    $sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    // If post not found
    if (!$post) {
        echo "<p>Post not found.</p>";
        exit;
    }
} else {
    echo "<p>No post selected.</p>";
    exit;
}

// Pagination setup
$limit = 5; // Number of posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $limit; // Calculate offset

// Fetch recent posts
$recentPosts = fetchRecentPosts($conn, $limit, $offset);
$trendingPosts = fetchTrendingPosts($conn);

// Get total number of posts for pagination
$totalPostsResult = $conn->query("SELECT COUNT(*) as count FROM posts");
$totalPosts = $totalPostsResult->fetch_assoc()['count'];
$totalPages = ceil($totalPosts / $limit);

// Update view count if a specific post is accessed
if (isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
    updateViewCount($conn, $postId);
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo htmlspecialchars($post['judul']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-starter.css">
</head>
<body>
    <header class="w3l-header">
        <nav class="navbar navbar-expand-lg navbar-light fill px-lg-0 py-0 px-3">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <span class="fa fa-pencil-square-o"></span> Web Programming Blog</a>
                <!-- Navbar content -->
            </div>
        </nav>
    </header>

    <div class="container">
        <h2><?php echo htmlspecialchars($post['judul']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($post['isi'])); ?></p>
        <?php if ($post['images']): ?>
            <img src="<?php echo htmlspecialchars($post['images']); ?>" alt="Image" style="max-width:100%; height:auto;">
        <?php endif; ?>
        <p><strong>Views:</strong> <?php echo htmlspecialchars($post['view']); ?></p>
    </div>

    <footer>
        <div class="container py-4">
            <p class="text-center">© 2024 Your Blog. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
