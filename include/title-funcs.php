<?php

/**
 * CS4750
 * Hoo's Watching
 * Jessica Heavner (jlh9qv), Julian Cornejo Castro (jac9vn), Patrick Thomas (pwt5ca), & Solimar Kwa (swk3st)
 */

require_once("db_interface.php");

define("SORT_TITLES_PRIMARY_TITLE", "primaryTitle");
define("SORT_TITLES_AVERAGE_RATING", "averageRating");
define("SORT_TITLES_NUM_VOTES", "numVotes");
define("SORT_TITLES_NUM_STARS", "averageRating*numVotes");
define("SORT_TITLES_YEAR", "startYear");
define("SORT_TITLES_LENGTH", "runtimeMinutes");
define("SORT_TITLE_USER_RATING", "userRating {order}, averageRating");
define("SORT_TITLE_NUM_USER_RATINGS", " numUserRatings {order}, numVotes");
$ALL_TITLE_SORTS = array(
    SORT_TITLES_PRIMARY_TITLE,
    SORT_TITLES_AVERAGE_RATING,
    SORT_TITLES_NUM_VOTES,
    SORT_TITLES_NUM_STARS,
    SORT_TITLES_YEAR,
    SORT_TITLES_LENGTH,
    SORT_TITLE_USER_RATING,
    SORT_TITLE_NUM_USER_RATINGS
);

define("FILTER_TITLES_NONE", "");
define("FILTER_TITLES_PRIMARY_TITLE", "WHERE primaryTitle LIKE \"%?%\"");
define("FILTER_TITLES_AVG_RATING", "WHERE averageRating > ?");
define("FILTER_TITLES_USER_RATING", " WHERE averageRating > ?");
define("FILTER_TITLES_GENRE", "WHERE Genres.genres=\"?\"");
define("FILTER_TITLES_TYPE", "WHERE titleType=\"?\"");
$ALL_TITLE_FILTERS = array(
    FILTER_TITLES_NONE,
    FILTER_TITLES_PRIMARY_TITLE,
    FILTER_TITLES_AVG_RATING,
    FILTER_TITLES_USER_RATING,
    FILTER_TITLES_GENRE,
    FILTER_TITLES_TYPE,
);

/**
 * Get titles in a certain range, sorted via one of many defined ways:
 * 
 * See the defined filters and sorts above.
 */
function get_titles($start, $end, $sort_type = SORT_TITLES_NUM_STARS, $filter_type = FILTER_TITLES_NONE, $filter_value = null, $ascending = true)
{
    global $db;
    global $ALL_TITLE_SORTS;
    global $ALL_TITLE_FILTERS;

    // Input validation.
    if ($end <= $start && $end > 0 && $start > 0) {
        die("get_titles: End of query range must be beyond the start point.");
    }
    if (!in_array($sort_type, $ALL_TITLE_SORTS)) {
        die("get_titles: Invalid sort type \"$sort_type\".");
    }
    if (!in_array($filter_type, $ALL_TITLE_FILTERS)) {
        die("get_titles: Invalid filter type \"$filter_type\".");
    }

    // Do some cleaning and replacing for the filter.
    $filter_value = mysqli_escape_string($db, $filter_value);
    $built_filter = str_replace("?", $filter_value, $filter_type);

    // Now build the sql command.
    $sql = "SELECT DISTINCT tconst, titleType, primaryTitle, originalTitle, isAdult, startYear, endYear, runtimeMinutes, averageRating, numVotes, (SELECT avg(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as userRating, (SELECT count(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as numUserRatings
    FROM Titles AS t
{filter}
ORDER BY {sort} {order}
LIMIT {start}, {count}";
    if ($filter_type == FILTER_TITLES_GENRE) {
        $sql = "SELECT DISTINCT tconst, titleType, primaryTitle, originalTitle, isAdult, startYear, endYear, runtimeMinutes, averageRating,numVotes, (SELECT avg(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as userRating, (SELECT count(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as numUserRatings
FROM Titles AS t
NATURAL JOIN Genres {filter}
ORDER BY {sort} {order}
LIMIT {start}, {count}";
    } else if ($filter_type == FILTER_TITLES_USER_RATING || $sort_type == SORT_TITLE_USER_RATING || $sort_type == SORT_TITLE_NUM_USER_RATINGS) {
        $sql = "SELECT DISTINCT tconst, titleType, primaryTitle, originalTitle, isAdult, startYear, endYear, runtimeMinutes, averageRating,numVotes, (SELECT avg(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as userRating, (SELECT count(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as numUserRatings
FROM Titles AS t
{filter}
ORDER BY {sort} {order}
LIMIT {start}, {count}";
    }

    // Ascending or descending.
    $order = "DESC";
    if ($ascending) {
        $order = "ASC";
    }

    // Replace in values.
    $sql = str_replace("{filter}", $built_filter, $sql);
    $sql = str_replace("{sort}", $sort_type, $sql);
    $sql = str_replace("{order}", $order, $sql);
    $sql = str_replace("{start}", strval((int) $start), $sql);
    $sql = str_replace("{count}", strval((int) $end - (int) $start), $sql);

    // debug_echo($sql);

    // Now perform the query.
    $statement = $db->prepare($sql);
    $statement->execute();

    $tconst = null;
    $titleType = null;
    $primaryTitle = null;
    $originalTitle = null;
    $isAdult = null;
    $startYear = null;
    $endYear = null;
    $runtimeMinutes = null;
    $averageRating = null;
    $numVotes = null;
    $userRating = null;
    $numUserVotes = null;

    $statement->bind_result($tconst, $titleType, $primaryTitle, $originalTitle, $isAdult, $startYear, $endYear, $runtimeMinutes, $averageRating, $numVotes, $userRating, $numUserVotes);
    $statement->fetch();

    $output = array();
    while ($statement->fetch()) {
        array_push($output, array(
            "tconst" => $tconst,
            "titleType" => $titleType,
            "primaryTitle" => $primaryTitle,
            "originalTitle" => $originalTitle,
            "isAdult" => $isAdult,
            "startYear" => $startYear,
            "endYear" => $endYear,
            "runtimeMinutes" => $runtimeMinutes,
            "averageRating" => $averageRating,
            "numVotes" => $numVotes,
            "userRating" => $userRating,
            "numUserVotes" => $numUserVotes,
        ));
    }

    $statement->close();

    return $output;
}

/**
 * Get information about a specific title.
 * 
 * @param str $tconst The title identifier to get the information about.
 * @return array
 */
function title_get_info($tconst)
{
    global $db;

    $sql = "SELECT tconst, titleType, primaryTitle, originalTitle, isAdult, startYear, endYear, runtimeMinutes, averageRating, numVotes, (SELECT avg(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as userRating, (SELECT count(number_of_stars) FROM UserToTitleData as ut WHERE ut.tconst = t.tconst) as numUserRatings
FROM Titles as t
WHERE tconst=?";

    // Now perform the query.
    $statement = $db->prepare($sql);
    $statement->bind_param("s", $tconst);
    $statement->execute();

    $tconst = null;
    $titleType = null;
    $primaryTitle = null;
    $originalTitle = null;
    $isAdult = null;
    $startYear = null;
    $endYear = null;
    $runtimeMinutes = null;
    $averageRating = null;
    $numVotes = null;
    $userRating = null;
    $numUserVotes = null;
    $statement->bind_result($tconst, $titleType, $primaryTitle, $originalTitle, $isAdult, $startYear, $endYear, $runtimeMinutes, $averageRating, $numVotes, $userRating, $numUserVotes);
    $statement->fetch();

    $output = array(
        "tconst" => $tconst,
        "titleType" => $titleType,
        "primaryTitle" => $primaryTitle,
        "originalTitle" => $originalTitle,
        "isAdult" => $isAdult,
        "startYear" => $startYear,
        "endYear" => $endYear,
        "runtimeMinutes" => $runtimeMinutes,
        "averageRating" => $averageRating,
        "numVotes" => $numVotes,
        "userRating" => $userRating,
        "numUserVotes" => $numUserVotes,
    );

    $statement->close();
    return $output;
}

/**
 * Get all of the comments for a specific title.
 * 
 * @param $tconst str Title identifier to get comments about.
 * @return array Array of comments, sorted by post date. Has the keys `email`, `date_added`, `text`, and `likes`.
 */
function title_get_comments($tconst)
{
    $sql = "SELECT email, date_added, text, likes FROM Comment WHERE tconst=? ORDER BY date_added ASC";

    global $db;

    $statement = $db->prepare($sql);
    $statement->bind_param("s", $tconst);
    $statement->execute();

    $email = null;
    $date_added = null;
    $text = null;
    $likes = null;
    $statement->bind_result($email, $date_added, $text, $likes);

    $output = array();
    while ($statement->fetch()) {
        array_push($output, array(
            "email" => $email,
            "date_added" => $date_added,
            "text" => $text,
            "likes" => $likes
        ));
    }

    $statement->close();

    return $output;
}

function title_get_people($tconst)
{
    $sql = "SELECT nconst, ordering, category, job, characters, primaryName, birthYear, deathYear, ( SELECT CONVERT(JSON_ARRAYAGG(primaryProfession) USING utf8) FROM Professions as p WHERE p.nconst = n.nconst GROUP BY n.nconst )
    FROM Names as n NATURAL JOIN PeopleToTitleData
    WHERE tconst=?
    ORDER BY ordering ASC";

    global $db;

    $statement = $db->prepare($sql);
    $statement->bind_param("s", $tconst);
    $statement->execute();

    $nconst = null;
    $ordering = null;
    $category = null;
    $job = null;
    $characters = null;
    $primaryName = null;
    $birthYear = null;
    $deathYear = null;
    $professions = null;
    $statement->bind_result($nconst, $ordering, $category, $job, $characters, $primaryName, $birthYear, $deathYear, $professions);

    $output = array();
    while ($statement->fetch()) {
        array_push($output, array(
            "nconst" => $nconst,
            "ordering" => $ordering,
            "category" => $category,
            "job" => $job,
            "characters" => json_decode($characters, true),
            "primaryName" => $primaryName,
            "birthYear" => $birthYear,
            "deathYear" => $deathYear,
            "professions" => json_decode($professions, true)
        ));
    }

    $statement->close();

    return $output;
}

function title_get_poster($tconst)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://imdb-api.com/en/API/Posters/" . IMDB_API_KEY . "/" . $tconst,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $json_data = json_decode($response, true)['posters'];

    // Find the first English poster (sorry)
    try {
        if (!is_array($json_data) || !isset($json_data[0])) {
            return null;
        }
        $poster = $json_data[0];
        foreach ($json_data as $p) {
            if ($p['language'] == "en") {
                $poster = $p;
                break;
            }
        }

        return $poster['link'];
    } catch (\Throwable $th) {
        return null;
    }
}

function title_get_genres($tconst)
{
    $sql = "SELECT CONVERT(JSON_ARRAYAGG(Genres) USING utf8) FROM Titles NATURAL JOIN Genres WHERE tconst=?";

    global $db;

    $statement = $db->prepare($sql);
    $statement->bind_param("s", $tconst);
    $statement->execute();

    $genres = null;
    $statement->bind_result($genres);
    $statement->fetch();
    $statement->close();

    return json_decode($genres, true);
}
