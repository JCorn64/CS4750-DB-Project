<html>
<?php

require_once("include/db_interface.php");
require_once("include/title-funcs.php");
require_once("include/user-funcs.php");
require_once("include/util.php");

$title = null;
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['tconst'])) {
        $title = title_get_info($_GET['tconst']);
    }
}

// Redirect back to the home page if the title isn't valid.
if (is_null($title)) {
    header("Location: ./index.php");
    die();
}

    <table
    id="table"
    data-url="json/data1.json"
    data-filter-control="true"
    data-show-search-clear-button="true">
    <thead>
        <tr>
        <th data-field="id">ID</th>
        <th data-field="name" data-filter-control="input">Item Name</th>
        <th data-field="price" data-filter-control="select">Item Price</th>
        </tr>
    </thead>
    </table>

    <script>
    $(function() {
        $('#table').bootstrapTable()
    })
    </script>


$HEADER_INFO = array(
    "Hoo's Watching | " . $title['primaryTitle'],
    $title['primaryTitle'] . " <small class='text-muted'> <a href=\"./index.php\">Hoo's Watching</a></small> ",
    "Hoo's Watching | " . $title['primaryTitle']
);
include("include/boilerplate/head.php");
?>
</html>