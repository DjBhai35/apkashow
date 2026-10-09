<?php
/**
 * ApkaShow - Live Search Suggestion API
 * Returns JSON suggestions for instant search typing
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, title, slug, poster, release_year, genre 
                          FROM movies 
                          WHERE status = 1 AND (title LIKE ? OR genre LIKE ? OR tags LIKE ? OR language LIKE ? OR release_year LIKE ?) 
                          ORDER BY (CASE WHEN title LIKE ? THEN 1 ELSE 2 END), views_count DESC, id DESC 
                          LIMIT 6");
    $term = '%' . $query . '%';
    $startsTerm = $query . '%';
    $stmt->execute([$term, $term, $term, $term, $term, $startsTerm]);
    $results = $stmt->fetchAll();

    $payload = [];
    foreach ($results as $row) {
        $payload[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'poster' => resolve_image_url($row['poster']),
            'release_year' => $row['release_year'],
            'genre' => $row['genre']
        ];
    }
    echo json_encode($payload);
} catch (Exception $e) {
    echo json_encode([]);
}
