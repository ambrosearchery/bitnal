<?php
// src/functions/availability.php

function f_is_date_fully_blocked(string $date): bool {
    return f_log_call('f_is_date_fully_blocked', function() use ($date) {
        $effective = f_get_effective_hours_for_date($date);
        return !$effective['is_open'] || 
               empty($effective['open_time']) || 
               empty($effective['close_time']);
    }, ['date' => $date]);
}

function f_get_available_dates(int $days_ahead = 30): array {
    return f_log_call('f_get_available_dates', function() use ($days_ahead) {
        $available_dates = [];
        $today = date('Y-m-d');
        
        for ($i = 0; $i < $days_ahead; $i++) {
            $date = date('Y-m-d', strtotime("$today + $i days"));
            
            if (f_is_date_fully_blocked($date)) continue;

            $effective = f_get_effective_hours_for_date($date);
            if (empty($effective['open_time']) || empty($effective['close_time'])) continue;

            $slots = f_get_available_times_for_date($date);
            
            if (!empty($slots)) {
                $available_dates[] = $date;
            }
        }
        return $available_dates;
    }, ['days_ahead' => $days_ahead]);
}

/**
 * Function 10 (Updated): Now uses real blocked lanes
 */
function f_get_available_times_for_date(string $date): array {
    return f_log_call('f_get_available_times_for_date', function() use ($date) {
        
        $effective = f_get_effective_hours_for_date($date);
        
        if (!$effective['is_open'] || empty($effective['open_time']) || empty($effective['close_time'])) {
            return [];
        }

        $open  = strtotime($effective['open_time']);
        $close = strtotime($effective['close_time']);
        $max_lanes = (int)$effective['lanes'];
        
        $slots = [];
        $current = $open;
        $slot_duration = 3600; // 1 hour

        while ($current + $slot_duration <= $close) {
            $time_str = date('H:i', $current);
            
            $blocked = f_get_blocked_lanes_for_slot($date, $time_str, 1);
            $lanes_available = max(0, $max_lanes - $blocked);

            $slots[] = [
                'time'            => $time_str,
                'lanes_available' => $lanes_available,
                'max_lanes'       => $max_lanes,
                'blocked_lanes'   => $blocked
            ];

            $current += $slot_duration;
        }
        return $slots;
    }, ['date' => $date]);
}
/**
 * Function 11: Main Availability Checker
 * Pure function - no side effects.
 * 
 * Checks if a specific slot is available for the requested duration and lanes.
 */
function f_check_availability(
    string $date, 
    string $start_time, 
    int $hours = 1, 
    int $lanes_needed = 1
): array {
    return f_log_call('f_check_availability', function() use ($date, $start_time, $hours, $lanes_needed) {
        
        // Get all available slots for the day
        $all_slots = f_get_available_times_for_date($date);
        
        if (empty($all_slots)) {
            return [
                'available' => false,
                'reason'    => 'Date is closed or no business hours',
                'lanes_available' => 0
            ];
        }

        $end_time = date('H:i', strtotime($start_time . " + $hours hours"));
        $required_slots = $hours;   // 1 hour per slot for now

        $available_lanes_in_window = 999; // Start high

        // Check each hour in the requested window
        for ($i = 0; $i < $required_slots; $i++) {
            $current_time = date('H:i', strtotime($start_time . " + $i hours"));
            
            $slot = array_filter($all_slots, fn($s) => $s['time'] === $current_time);
            $slot = reset($slot);

            if (!$slot) {
                return [
                    'available' => false,
                    'reason'    => "No slot at $current_time",
                    'lanes_available' => 0
                ];
            }

            $available_lanes_in_window = min($available_lanes_in_window, $slot['lanes_available']);
        }

        $is_available = $available_lanes_in_window >= $lanes_needed;

        return [
            'available'       => $is_available,
            'lanes_available' => $available_lanes_in_window,
            'lanes_needed'    => $lanes_needed,
            'start_time'      => $start_time,
            'end_time'        => $end_time,
            'hours'           => $hours,
            'date'            => $date,
            'reason'          => $is_available ? 'Slot is available' : 'Not enough lanes available'
        ];
        
    }, [
        'date' => $date,
        'start_time' => $start_time,
        'hours' => $hours,
        'lanes_needed' => $lanes_needed
    ]);
}
/**
 * Function 12: Get Blocked Lanes for a Specific Time Slot
 * Pure read function.
 * 
 * Returns how many lanes are already booked in a given time window.
 * (Currently returns 0 - will become real when we build bookings table)
 */
function f_get_blocked_lanes_for_slot(
    string $date, 
    string $start_time, 
    int $hours = 1
): int {
    return f_log_call('f_get_blocked_lanes_for_slot', function() use ($date, $start_time, $hours) {
        
        // TODO: Later we will query the bookings table
        // For now: return 0 (no bookings yet)
        
        // Example future query:
        // $blocked = f_db_query("
        //     SELECT SUM(lanes_requested) as total_blocked
        //     FROM bookings 
        //     WHERE booking_date = ? 
        //       AND start_time <= ? 
        //       AND end_time > ?
        //       AND status IN ('confirmed', 'pending')
        // ", [$date, $start_time, $start_time])->fetch();
        
        // return (int)($blocked['total_blocked'] ?? 0);
        
        return 0;   // Placeholder
        
    }, ['date' => $date, 'start_time' => $start_time, 'hours' => $hours]);
}
/**
 * Function 13: Handle New Booking - Adapted to your actual table structure
 */
function f_handle_new_booking(array $data): array {
    return f_log_call('f_handle_new_booking', function() use ($data) {
        
        // 1. Validation
        $required = ['date', 'start_time', 'hours', 'customer_name', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Missing field: $field"];
            }
        }

        $date          = $data['date'];
        $start_time    = $data['start_time'];
        $hours         = (int)$data['hours'];
        $lanes         = (int)($data['lanes'] ?? 1);           // We'll store in num_people for now
        $customer_name = trim($data['customer_name']);
        $phone         = trim($data['phone']);
        $email         = trim($data['email'] ?? '');
        $notes         = trim($data['notes'] ?? '');

        // 2. Check Availability
        $avail = f_check_availability($date, $start_time, $hours, $lanes);
        if (!$avail['available']) {
            return ['success' => false, 'error' => $avail['reason']];
        }

        // 3. Insert using your actual column names
        try {
            f_db_query("
                INSERT INTO bookings 
                (booking_date, start_time, hours, num_people, 
                 customer_name, phone, email, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())
            ", [
                $date,
                $start_time,
                $hours,
                $lanes,                    // using num_people column
                $customer_name,
                $phone,
                $email,
                $notes
            ]);

            $booking_id = (int) $GLOBALS['pdo']->lastInsertId();

            return [
                'success'     => true,
                'booking_id'  => $booking_id,
                'message'     => 'Booking created successfully!'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => 'Database error: ' . $e->getMessage()
            ];
        }
    }, $data);
}
?>