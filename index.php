<?php
// Database connection parameters
$host = "localhost"; // Change if needed
$user = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$database = "berita"; // Name of your database

// Create a connection
$conn = new mysqli($host, $user, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to update the view count
function updateViewCount($conn, $postId) {
    $sql = "UPDATE posts SET view = view + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $conn->error); // Error handling
    }
    $stmt->bind_param("i", $postId);
    if ($stmt->execute()) {
        $stmt->close();
        return true; // Successfully updated
    } else {
        echo "Failed to update view count: " . $stmt->error; // Error handling
        $stmt->close();
        return false; // Failed to update
    }
}

// Function to fetch recent posts with pagination
function fetchRecentPosts($conn, $limit, $offset) {
    $sql = "SELECT id, judul, isi, images, view FROM posts ORDER BY tanggal_publikasi DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $conn->error); // Error handling
    }
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch trending posts
function fetchTrendingPosts($conn) {
    $sql = "SELECT judul, isi, images, view FROM posts ORDER BY view DESC LIMIT 5"; // Adjust limit as needed
    return $conn->query($sql);
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
    if (updateViewCount($conn, $postId)) {
        echo "View count updated successfully.";
    } else {
        echo "Failed to update view count.";
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Web Programming - Final Semester Exam</title>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <style>
        /* Add your CSS styles here */
        .sidebar {
            float: right;
            width: 30%;
        }
        .main-content {
            float: left;
            width: 65%;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #007bff;
            color: #007bff;
            text-decoration: none;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <!-- header -->
    <header class="w3l-header">
        <nav class="navbar navbar-expand-lg navbar-light fill px-lg-0 py-0 px-3">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <span class="fa fa-pencil-square-o"></span> Web Programming Blog</a>
                <button class="navbar-toggler collapsed" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="fa icon-expand fa-bars"></span>
                    <span class="fa icon-close fa-times"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown @@category__active">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Categories <span class="fa fa-angle-down"></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item @@cp__active" href="technology.php">Technology posts</a>
                                <a class="dropdown-item @@ls__active" href="lifestyle.php">Lifestyle posts</a>
                            </div>
                        </li>
                        <li class="nav-item @@about__active">
                            <a class="nav-link" href="crud.php">Admin Dashboard</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="w3l-homeblock1">
        <div class="container pt-lg-5 pt-md-4">
            <div class="main-content">
                <h2 class="mb-4">Recent Posts</h2>
                <?php
                if ($recentPosts) {
                    if ($recentPosts->num_rows > 0) {
                        while ($row = $recentPosts->fetch_assoc()) {
                            echo "<div class='post'>";
                            echo "<h3><a href='artikel.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['judul']) . "</a></h3>";
                            echo "<p>" . nl2br(htmlspecialchars($row['isi'])) . "</p>";
                            echo "<p><strong>Views:</strong> " . htmlspecialchars($row['view']) . "</p>";
                            if ($row['images']) {
                                echo "<img src='uploads/" . htmlspecialchars($row['images']) . "' alt='Image' style='max-width:100%; height:auto;'>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No recent posts available.</p>";
                    }
                } else {
                    echo "<p>Error fetching recent posts.</p>";
                }
                ?>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar">
                <h2 class="mb-4">Trending Posts</h2>
                <?php
                if ($trendingPosts) {
                    if ($trendingPosts->num_rows > 0) {
                        while ($row = $trendingPosts->fetch_assoc()) {
                            echo "<div class='trending-post'>";
                            echo "<h5>" . htmlspecialchars($row['judul']) . "</h5>";
                            echo "<p><strong>Views:</strong> " . htmlspecialchars($row['view']) . "</p>";
                            echo "<p>" . nl2br(htmlspecialchars($row['isi'])) . "</p>";
                            if ($row['images']) {
                                echo "<img src='uploads/" . htmlspecialchars($row['images']) . "' alt='Image' style='max-width:100%; height:auto;'>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No trending posts available.</p>";
                    }
                } else {
                    echo "<p>Error fetching trending posts.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 Web Programming. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
