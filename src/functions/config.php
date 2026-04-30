<?php
// src/functions/config.php
// Configuration & Setup Functions (Phase 2)

/**
 * Function 5: Save Normal Weekly Hours
 */
function f_save_normal_hours(array $data): bool {
    return f_log_call('f_save_normal_hours', function() use ($data) {
        
        // Clear old data
        f_db_query("TRUNCATE TABLE business_hours");

        $inserted = 0;
        for ($i = 0; $i < 7; $i++) {
            $open  = $data['normal'][$i]['open']  ?? null;
            $close = $data['normal'][$i]['close'] ?? null;
            $lanes = (int)($data['normal'][$i]['lanes'] ?? 12);

            f_db_query("
                INSERT INTO business_hours 
                (day_of_week, open_time, close_time, default_lanes)
                VALUES (?, ?, ?, ?)
            ", [$i, $open, $close, $lanes]);

            $inserted++;
        }

        return true;
    }, $data);
}

/**
 * Function 6: Save Date Override
 */
function f_save_date_override(array $data): bool {
    return f_log_call('f_save_date_override', function() use ($data) {
        
        $override_date = $data['override_date'] ?? null;
        $description   = trim($data['description'] ?? 'No description');
        $open_time     = !empty($data['open_time']) ? $data['open_time'] : null;
        $close_time    = !empty($data['close_time']) ? $data['close_time'] : null;
        $lanes         = !empty($data['default_lanes']) ? (int)$data['default_lanes'] : null;
        $is_closed     = isset($data['is_closed']) && $data['is_closed'] == 'on' ? 1 : 0;

        if (empty($override_date)) {
            throw new Exception("Date is required");
        }

        f_db_query("
            INSERT INTO date_overrides 
            (override_date, open_time, close_time, available_lanes, is_closed, reason, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                open_time = VALUES(open_time),
                close_time = VALUES(close_time),
                available_lanes = VALUES(available_lanes),
                is_closed = VALUES(is_closed),
                reason = VALUES(reason),
                description = VALUES(description)
        ", [$override_date, $open_time, $close_time, $lanes, $is_closed, $description, $description]);

        return true;
    }, $data);
}

/**
 * Function 7: Get Effective Hours for a Specific Date
 */
function f_get_effective_hours_for_date(string $date): array {
    return f_log_call('f_get_effective_hours_for_date', function() use ($date) {
        
        $weekday = (int)date('w', strtotime($date));

        $normal = f_db_query("
            SELECT open_time, close_time, default_lanes 
            FROM business_hours 
            WHERE day_of_week = ? 
            LIMIT 1
        ", [$weekday])->fetch(PDO::FETCH_ASSOC);

        $override = f_db_query("
            SELECT open_time, close_time, available_lanes, is_closed, reason, description
            FROM date_overrides 
            WHERE override_date = ? 
            LIMIT 1
        ", [$date])->fetch(PDO::FETCH_ASSOC);

        $result = [
            'date'       => $date,
            'weekday'    => $weekday,
            'is_open'    => true,
            'open_time'  => null,
            'close_time' => null,
            'lanes'      => 12,
            'source'     => 'normal',
            'note'       => ''
        ];

        if ($override) {
            $result['source'] = 'override';
            $result['note']   = $override['reason'] ?? $override['description'] ?? '';

            if (!empty($override['is_closed']) && $override['is_closed'] == 1) {
                $result['is_open'] = false;
            } else {
                $result['open_time']  = $override['open_time'];
                $result['close_time'] = $override['close_time'];
                $result['lanes']      = (int)($override['available_lanes'] ?? 12);
            }
        } elseif ($normal) {
            $result['open_time']  = $normal['open_time'];
            $result['close_time'] = $normal['close_time'];
            $result['lanes']      = (int)($normal['default_lanes'] ?? 12);
        }

        return $result;
    }, ['date' => $date]);
}

/**
 * Function 8: Get Full Business Configuration
 */
function f_get_business_config(): array {
    return f_log_call('f_get_business_config', function() {
        
        $normal_hours = f_db_query("
            SELECT * FROM business_hours 
            ORDER BY day_of_week ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $overrides = f_db_query("
            SELECT * FROM date_overrides 
            WHERE override_date >= CURDATE() 
            ORDER BY override_date ASC 
            LIMIT 50
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'normal_hours' => $normal_hours,
            'overrides'    => $overrides,
            'today'        => date('Y-m-d'),
            'status'       => 'success'
        ];
    });
}
?>