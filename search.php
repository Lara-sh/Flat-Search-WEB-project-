<?php
session_start();
require_once 'database.inc.php';
$pdo = db_connect();
require_once 'Search.php';
class Search {
    private $pdo;
    private $results = [];
    private $sortCol;
    private $sortDir;

    // Allowed columns for sorting
    private $allowedSortCols = ['ref_number', 'price', 'available_date', 'location', 'bedrooms', 'bathrooms', 'furnished'];

    public function __construct($pdo) {
        $this->pdo = $pdo;

        // Determine sorting from GET or cookies or default
        $this->sortCol = $_GET['sort_col'] ?? ($_COOKIE['sort_col'] ?? 'price');
        $this->sortDir = strtolower($_GET['sort_dir'] ?? ($_COOKIE['sort_dir'] ?? 'asc'));

        // Validate sort column
        if (!in_array($this->sortCol, $this->allowedSortCols)) {
            $this->sortCol = 'price';
        }
        // Validate sort direction
        if ($this->sortDir !== 'asc' && $this->sortDir !== 'desc') {
            $this->sortDir = 'asc';
        }

        // Save sorting preferences in cookies (expire in 30 days)
        setcookie('sort_col', $this->sortCol, time() + 86400 * 30, "/");
        setcookie('sort_dir', $this->sortDir, time() + 86400 * 30, "/");
    }

    public function handle() {
        echo '<section>';
        $this->showForm();
        $this->processSearch();
        $this->showResults();
        echo '</section>';
    }

    private function showForm() {
        $minPrice = $_GET['min_price'] ?? '';
        $maxPrice = $_GET['max_price'] ?? '';
        $location = $_GET['location'] ?? '';
        $bedrooms = $_GET['bedrooms'] ?? '';
        $bathrooms = $_GET['bathrooms'] ?? '';
        $furnished = $_GET['furnished'] ?? '';

        echo <<<FORM
<form method="get" class="search-form">
    <label>Min Price:
        <input type="number" name="min_price" value="$minPrice" min="0" />
    </label>
    <label>Max Price:
        <input type="number" name="max_price" value="$maxPrice" min="0" />
    </label>
    <label>Location:
        <input type="text" name="location" value="$location" placeholder="Enter location" />
    </label>
    <label>Bedrooms:
        <input type="number" name="bedrooms" value="$bedrooms" min="0" />
    </label>
    <label>Bathrooms:
        <input type="number" name="bathrooms" value="$bathrooms" min="0" />
    </label>
    <label>Furnished:
        <select name="furnished">
            <option value="" " . ($furnished === '' ? 'selected' : '') . ">Any</option>
            <option value="yes" " . ($furnished === 'yes' ? 'selected' : '') . ">Yes</option>
            <option value="no" " . ($furnished === 'no' ? 'selected' : '') . ">No</option>
        </select>
    </label>
    <button type="submit">Search</button>
</form>
FORM;
    }

    private function processSearch() {
    $sql = "SELECT * FROM flats WHERE rented = 0 AND is_approved = 1";
    $params = [];

    if (!empty($_GET['min_price'])) {
        $sql .= " AND price >= :min_price";
        $params[':min_price'] = (float)$_GET['min_price'];
    }
    if (!empty($_GET['max_price'])) {
        $sql .= " AND price <= :max_price";
        $params[':max_price'] = (float)$_GET['max_price'];
    }
    if (!empty($_GET['location'])) {
        $sql .= " AND location LIKE :location";
        $params[':location'] = '%' . $_GET['location'] . '%';
    }
    if (!empty($_GET['bedrooms'])) {
        $sql .= " AND bedrooms = :bedrooms";
        $params[':bedrooms'] = (int)$_GET['bedrooms'];
    }
    if (!empty($_GET['bathrooms'])) {
        $sql .= " AND bathrooms = :bathrooms";
        $params[':bathrooms'] = (int)$_GET['bathrooms'];
    }
    if (isset($_GET['furnished']) && ($_GET['furnished'] === 'yes' || $_GET['furnished'] === 'no')) {
        $furnished_val = ($_GET['furnished'] === 'yes') ? 1 : 0;
        $sql .= " AND furnished = :furnished";
        $params[':furnished'] = $furnished_val;
    }

    // Order by selected column and direction
    $sql .= " ORDER BY {$this->sortCol} {$this->sortDir}";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $this->results = $stmt->fetchAll();
}

private function showResults() {
    if (empty($this->results)) {
        echo "<p>No flats found matching your criteria.</p>";
        return;
    }

    function sortLink($col, $label, $currentCol, $currentDir) {
        $dir = 'asc';
        $icon = '';
        if ($col === $currentCol) {
            if ($currentDir === 'asc') {
                $dir = 'desc';
                $icon = '▲ ';
            } else {
                $dir = 'asc';
                $icon = '▼ ';
            }
        }
        $params = $_GET;
        $params['sort_col'] = $col;
        $params['sort_dir'] = $dir;
        $query = http_build_query($params);

        return "<a href=\"?{$query}\">{$icon}{$label}</a>";
    }

    echo "<table>";
    echo "<thead><tr>";
    echo "<th>" . sortLink('ref_number', 'Flat Reference', $this->sortCol, $this->sortDir) . "</th>";
    echo "<th>Photo</th>";
    echo "<th>" . sortLink('price', 'Monthly Rental Cost', $this->sortCol, $this->sortDir) . "</th>";
    echo "<th>" . sortLink('available_from', 'Availability Date', $this->sortCol, $this->sortDir) . "</th>";
    echo "<th>" . sortLink('location', 'Location', $this->sortCol, $this->sortDir) . "</th>";
    echo "<th>" . sortLink('bedrooms', 'Bedrooms', $this->sortCol, $this->sortDir) . "</th>";
    echo "<th>" . sortLink('bathrooms', 'Bathrooms', $this->sortCol, $this->sortDir) . "</th>";
    echo "<th>" . sortLink('furnished', 'Furnished', $this->sortCol, $this->sortDir) . "</th>";
    echo "</tr></thead>";

    echo "<tbody>";
    foreach ($this->results as $flat) {
    $ref = htmlentities($flat['ref_number']);
    $price = htmlentities($flat['price']);
    $location = htmlentities($flat['location']);
    $bedrooms = htmlentities($flat['bedrooms']);
    $bathrooms = htmlentities($flat['bathrooms']);
    $furnished = $flat['furnished'] ? 'Yes' : 'No';

    $available_from = htmlentities($flat['available_from'] ?? '');
    $available_to = htmlentities($flat['available_to'] ?? '');
    $available_range = $available_from && $available_to ? "$available_from - $available_to" : (htmlentities($flat['available_date'] ?? ''));

    // Use flat ID folder, not ref number folder
    $photoFolder = "uploads/flats/" . $flat['id'] . "/";
    $photoFile = "";

    if (is_dir($photoFolder)) {
        $files = scandir($photoFolder);
        foreach ($files as $file) {
            if (preg_match('/^photo_.*\.(jpg|jpeg|png|gif)$/i', $file)) {
                $photoFile = $photoFolder . $file;
                break;
            }
        }
    }

    if (!$photoFile) {
        $photoFile = "uploads/default_photo.jpg"; // fallback photo
    }

    $photo_link = "<a href='detailpage.php?ref=$ref' target='_blank'><img src='$photoFile' alt='Flat Photo' style='width:100px; height:auto;' /></a>";

    echo "<tr>";
    echo "<td>$ref</td>";
    echo "<td>$photo_link</td>";
    echo "<td>$price</td>";
    echo "<td>$available_range</td>";
    echo "<td>$location</td>";
    echo "<td>$bedrooms</td>";
    echo "<td>$bathrooms</td>";
    echo "<td>$furnished</td>";
    echo "</tr>";
}
    echo "</tbody></table>";
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flat Search - Lara Flat Rent</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include("navigation.php"); ?>
    
    <main>
        <h2>Search for Flats</h2>
        <?php
        $search = new Search($pdo);
        $search->handle();
        ?>
    </main>
</div>

<?php include 'footer.php'; ?>
</body>
</html>