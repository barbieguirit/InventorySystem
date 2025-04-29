<?php

/**
 * Validate if a foreign key value exists in a table.
 *
 * @param PDO $pdo The PDO database connection.
 * @param string $table The table name to check.
 * @param string $column The column name to check.
 * @param mixed $value The value to validate.
 * @return bool True if the value exists, false otherwise.
 */
function validateForeignKey(PDO $pdo, $table, $column, $value) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
    $stmt->execute([$value]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Log user actions to the AuditLogs table.
 *
 * @param PDO $pdo The PDO database connection.
 * @param int $userID The ID of the user performing the action.
 * @param string $action The action performed (e.g., "Added", "Edited", "Deleted").
 * @param string $tableName The name of the table where the action occurred.
 * @param int $recordID The ID of the record affected by the action.
 */
function logAction(PDO $pdo, $userID, $action, $tableName, $recordID) {
    $stmt = $pdo->prepare("INSERT INTO AuditLogs (UserID, Action, TableName, RecordID) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userID, $action, $tableName, $recordID]);
}


/**
 * Fetch paginated records with search functionality.
 *
 * @param PDO $pdo The PDO database connection.
 * @param string $table The name of the table to query.
 * @param string $searchColumn The column to search in.
 * @param string $searchTerm The search term.
 * @param int $limit The number of records per page.
 * @param int $offset The offset for pagination.
 * @return array The paginated records and total record count.
 */

function fetchPaginatedRecords(PDO $pdo, $table, $searchColumn, $searchTerm, $limit, $offset) {
    // Fetch total number of records with search filter
    $totalQuery = "SELECT COUNT(*) AS total FROM $table WHERE $searchColumn LIKE :search";
    $totalStmt = $pdo->prepare($totalQuery);
    $totalStmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
    $totalStmt->execute();
    $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
    $totalRecords = $totalRow['total'];

    
    // Fetch paginated records with search filter
    $query = "SELECT * FROM $table WHERE $searchColumn LIKE :search LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'records' => $records,
        'totalRecords' => $totalRecords
    ];
}

/**
 * Generate pagination links.
 *
 * @param int $currentPage The current page number.
 * @param int $totalPages The total number of pages.
 * @param string $baseUrl The base URL for pagination links.
 * @param string $searchTerm The search term to include in the query string.
 * @return string The HTML for pagination links.
 */
function generatePaginationLinks($currentPage, $totalPages, $baseUrl, $searchTerm = '') {
    $html = '<nav><ul class="pagination justify-content-center">';

    // Previous link
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '&search=' . urlencode($searchTerm) . '">Previous</a></li>';
    }

    // Page links
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $html .= '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '&search=' . urlencode($searchTerm) . '">' . $i . '</a></li>';
    }

    // Next link
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '&search=' . urlencode($searchTerm) . '">Next</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
?>