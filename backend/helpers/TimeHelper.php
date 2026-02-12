<?php
/**
 * Time Helper
 * Handles date and time calculations
 */

class TimeHelper {
    /**
     * Calculate days remaining until date
     */
    public static function daysUntil($targetDate) {
        $today = new DateTime();
        $target = new DateTime($targetDate);
        $diff = $today->diff($target);
        
        return $diff->days * ($diff->invert ? -1 : 1);
    }
    
    /**
     * Get weeks pregnant
     */
    public static function getWeeksPregnant($lastMenstrualDate) {
        $lmd = new DateTime($lastMenstrualDate);
        $today = new DateTime();
        $diff = $lmd->diff($today);
        
        return (int)($diff->days / 7);
    }
    
    /**
     * Calculate estimated due date from LMP
     */
    public static function getEstimatedDueDate($lastMenstrualDate) {
        $date = new DateTime($lastMenstrualDate);
        $date->add(new DateInterval('P280D')); // 280 days = ~40 weeks
        
        return $date->format('Y-m-d');
    }
    
    /**
     * Is date today?
     */
    public static function isToday($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }
    
    /**
     * Format time ago
     */
    public static function timeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) return $diff . ' seconds ago';
        $diff = round($diff / 60);
        if ($diff < 60) return $diff . ' minutes ago';
        $diff = round($diff / 60);
        if ($diff < 24) return $diff . ' hours ago';
        $diff = round($diff / 24);
        if ($diff < 30) return $diff . ' days ago';
        $diff = round($diff / 30);
        if ($diff < 12) return $diff . ' months ago';
        $diff = round($diff / 12);
        return $diff . ' years ago';
    }
    
    /**
     * Format date to readable format
     */
    public static function formatDate($date, $format = 'M d, Y') {
        return date($format, strtotime($date));
    }
    
    /**
     * Check if appointment is overdue
     */
    public static function isOverdue($date) {
        return strtotime($date) < time();
    }
    
    /**
     * Check if appointment is upcoming (within 7 days)
     */
    public static function isUpcoming($date) {
        $days = self::daysUntil($date);
        return $days >= 0 && $days <= 7;
    }
}

?>
