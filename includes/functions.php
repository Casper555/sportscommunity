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
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();

}

function getAllEvents(PDO $pdo, ?string $type = null, ?string $search = null): array 
{
    $sql = "
        SELECT e.*, COUNT(r.id) AS registration_count 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id
    ";

    $where = [];
    $params = [];

    if (!empty($type)) {
        $where[] = "e.type = :type";
        $params[':type'] = $type;
    }

    if (!empty($search)) {
        $where[] = "(e.title LIKE :s_title OR e.city LIKE :s_city OR e.organizer_name LIKE :s_org)";
        $searchTerm = '%' . $search . '%';
        $params[':s_title'] = $searchTerm;
        $params[':s_city']  = $searchTerm;
        $params[':s_org']   = $searchTerm;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " GROUP BY e.id ORDER BY e.event_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
function getEventById(PDO $pdo, int $id): ?array 
{
    $stmt = $pdo->prepare("
        SELECT e.*, COUNT(r.id) AS registration_count 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id 
        WHERE e.id = :id
        GROUP BY e.id
    ");
    $stmt->execute([':id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    return $event ?: null;
}

function registerForEvent(PDO $pdo, int $userId, int $eventId): bool 
{
    // Check if user is already registered
    $checkStmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = :user_id AND event_id = :event_id");
    $checkStmt->execute([':user_id' => $userId, ':event_id' => $eventId]);
    
    if ($checkStmt->fetch()) {
        return false; // Already registered
    }

    // Insert new registration
    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id, created_at) VALUES (:user_id, :event_id, NOW())");
    return $stmt->execute([':user_id' => $userId, ':event_id' => $eventId]);
}