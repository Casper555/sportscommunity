<?php
function getUpcomingEvents(PDO $pdo, int $limit = 3): array 
{
    $stmt = $pdo->prepare("
        SELECT e.*, COUNT(r.id) AS registration_count 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id 
        GROUP BY e.id 
        ORDER BY e.event_date ASC 
        LIMIT :limit
    ");
    
    // Bind integer parameter safely
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();

}

function getAllEvents(PDO $pdo, ?string $type = null, ?string $search = null): array 
{
    // 1. Base SQL query structure
    $sql = "
        SELECT e.*, COUNT(r.id) AS registration_count 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id
    ";

    $where = [];
    $params = [];

    // 2. Filter by category/type if specified
    if (!empty($type)) {
        $where[] = "e.type = :type";
        $params[':type'] = $type;
    }

    // 3. Filter by search term with unique placeholders
    if (!empty($search)) {
        $where[] = "(e.title LIKE :s_title OR e.city LIKE :s_city OR e.organizer_name LIKE :s_org)";
        $searchTerm = '%' . $search . '%';
        $params[':s_title'] = $searchTerm;
        $params[':s_city']  = $searchTerm;
        $params[':s_org']   = $searchTerm;
    }

    // 4. Attach WHERE clauses dynamically if conditions exist
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // 5. Append grouping and sorting
    $sql .= " GROUP BY e.id ORDER BY e.event_date ASC";

    // 6. Prepare, execute safely, and return results
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}