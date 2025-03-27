<?php
require_once '../../db/db_conn.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                e.position,
                e.pfp,
                (
                    (AVG((ev.quality + ev.communication_skills + ev.teamwork + ev.punctuality + ev.initiative) / 5) * 0.7) + 
                    (AVG((ptp.quality + ptp.communication_skills + ptp.teamwork + ptp.punctuality + ptp.initiative) / 5) * 0.3)
                ) as weighted_avg_score,
                AVG((ev.quality + ev.communication_skills + ev.teamwork + ev.punctuality + ev.initiative) / 5) as admin_avg,
                AVG((ptp.quality + ptp.communication_skills + ptp.teamwork + ptp.punctuality + ptp.initiative) / 5) as ptp_avg
              FROM employee_register e
              LEFT JOIN evaluations ev ON e.employee_id = ev.employee_id
              LEFT JOIN ptp_evaluations ptp ON e.employee_id = ptp.employee_id
              GROUP BY e.employee_id
              HAVING weighted_avg_score IS NOT NULL
              ORDER BY weighted_avg_score DESC
              LIMIT 5";
    
    $result = $conn->query($query);
    $performers = [];
    
    while ($row = $result->fetch_assoc()) {
        $performers[] = [
            'employee_id' => $row['employee_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'position' => $row['position'],
            'pfp' => $row['pfp'],
            'avg_score' => (float)$row['weighted_avg_score'],
            'admin_score' => (float)$row['admin_avg'],
            'ptp_score' => (float)$row['ptp_avg']
        ];
    }
    
    echo json_encode($performers);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>