<?php
function getUpcomingEvents(PDO $pdo, int $limit = 3, ?int $excludeId = null): array 
{
    $sql = "
        SELECT e.*, COUNT(r.id) AS registration_count 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id
    ";

    $params = [];
    if ($excludeId !== null) {
        $sql .= " WHERE e.id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }

    $sql .= " GROUP BY e.id ORDER BY e.event_date ASC LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    // Bind parameters safely
    if ($excludeId !== null) {
        $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
    }
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
        $where[] = "(e.title LIKE :s_title OR e.city LIKE :s_city OR e.organizer_name LIKE :s_org OR e.contact_person LIKE :s_person)";
        $searchTerm = '%' . $search . '%';
        $params[':s_title'] = $searchTerm;
        $params[':s_city']  = $searchTerm;
        $params[':s_org']   = $searchTerm;
        $params[':s_person']   = $searchTerm;
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
    // 1. Check event capacity limit (using unique placeholder names to prevent HY093 error)
    $eventStmt = $pdo->prepare("
        SELECT max_participants, 
               (SELECT COUNT(*) FROM registrations WHERE event_id = :event_id_sub) as current_count 
        FROM events WHERE id = :event_id_main
    ");
    $eventStmt->execute([
        ':event_id_sub'  => $eventId,
        ':event_id_main' => $eventId
    ]);
    $eventData = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if ($eventData && $eventData['max_participants'] !== null && $eventData['current_count'] >= $eventData['max_participants']) {
        return false; // Event is full
    }

    // 2. Check if user is already registered
    $checkStmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = :user_id AND event_id = :event_id");
    $checkStmt->execute([':user_id' => $userId, ':event_id' => $eventId]);
    
    if ($checkStmt->fetch()) {
        return false; // Already registered
    }

    // 3. Insert new registration
    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id, registration_date) VALUES (:user_id, :event_id, NOW())");
    return $stmt->execute([':user_id' => $userId, ':event_id' => $eventId]);
}

function loginUser(PDO $pdo, string $email, string $password): ?array 
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return null;
}
function registerUser(PDO $pdo, string $username, string $email, string $password): bool 
{
    // Check if email or username is already taken
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
    $checkStmt->execute([':email' => $email, ':username' => $username]);
    if ($checkStmt->fetch()) {
        return false;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
    return $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);
}
function isUserRegistered($pdo, $userId, $eventId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$userId, $eventId]);
    return $stmt->fetchColumn() > 0;
}   

function unregisterFromEvent($pdo, $userId, $eventId) {
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    return $stmt->execute([$userId, $eventId]);
}
function createEvent(PDO $pdo, array $data): bool 
{
    $sql = "INSERT INTO events (
        user_id, title, type, description, image, event_date, start_time, end_time,
        location_name, street, postal_code, city, organizer_name, contact_person,
        contact_email, target_group, max_participants, price, registration_deadline, created_at
    ) VALUES (
        :user_id, :title, :type, :description, :image, :event_date, :start_time, :end_time,
        :location_name, :street, :postal_code, :city, :organizer_name, :contact_person,
        :contact_email, :target_group, :max_participants, :price, :registration_deadline, NOW()
    )";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':user_id'               => $data['user_id'],
        ':title'                 => $data['title'],
        ':type'                  => $data['type'],
        ':description'           => $data['description'],
        ':image'                 => $data['image'],
        ':event_date'            => $data['event_date'],
        ':start_time'            => $data['start_time'],
        ':end_time'              => $data['end_time'],
        ':location_name'         => $data['location_name'],
        ':street'                => $data['street'],
        ':postal_code'           => $data['postal_code'],
        ':city'                  => $data['city'],
        ':organizer_name'        => $data['organizer_name'],
        ':contact_person'        => $data['contact_person'],
        ':contact_email'         => $data['contact_email'],
        ':target_group'          => $data['target_group'],
        ':max_participants'      => $data['max_participants'],
        ':price'                 => $data['price'],
        ':registration_deadline' => $data['registration_deadline']
    ]);
}

function getUserById(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUserRegisteredEvents(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT e.*, r.registration_date 
        FROM events e
        JOIN registrations r ON e.id = r.event_id
        WHERE r.user_id = :user_id
        ORDER BY e.event_date ASC
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserCreatedEvents(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT e.*, COUNT(r.id) AS registration_count
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
        WHERE e.user_id = :user_id
        GROUP BY e.id
        ORDER BY e.event_date ASC
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateEvent(PDO $pdo, int $eventId, int $userId, array $data): bool 
{
    $sql = "UPDATE events SET 
        title = :title, 
        type = :type, 
        description = :description, 
        image = :image, 
        event_date = :event_date, 
        start_time = :start_time, 
        end_time = :end_time,
        location_name = :location_name, 
        street = :street, 
        postal_code = :postal_code, 
        city = :city, 
        organizer_name = :organizer_name, 
        contact_person = :contact_person,
        contact_email = :contact_email, 
        target_group = :target_group, 
        max_participants = :max_participants, 
        price = :price, 
        registration_deadline = :registration_deadline
    WHERE id = :id AND user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id'                    => $eventId,
        ':user_id'               => $userId,
        ':title'                 => $data['title'],
        ':type'                  => $data['type'],
        ':description'           => $data['description'],
        ':image'                 => $data['image'],
        ':event_date'            => $data['event_date'],
        ':start_time'            => $data['start_time'],
        ':end_time'              => $data['end_time'],
        ':location_name'         => $data['location_name'],
        ':street'                => $data['street'],
        ':postal_code'           => $data['postal_code'],
        ':city'                  => $data['city'],
        ':organizer_name'        => $data['organizer_name'],
        ':contact_person'        => $data['contact_person'],
        ':contact_email'         => $data['contact_email'],
        ':target_group'          => $data['target_group'],
        ':max_participants'      => $data['max_participants'],
        ':price'                 => $data['price'],
        ':registration_deadline' => $data['registration_deadline']
    ]);
}

function getEventParticipants(PDO $pdo, int $eventId): array {
    $stmt = $pdo->prepare("
        SELECT u.username, u.email, r.registration_date 
        FROM users u
        JOIN registrations r ON u.id = r.user_id
        WHERE r.event_id = :event_id
        ORDER BY r.registration_date ASC
    ");
    $stmt->execute([':event_id' => $eventId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}