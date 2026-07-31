<?php

namespace App\Support;

class DurationFormatter
{
    public static function daysWithParenthetical(int $days): string
    {
        $days = max(0, $days);
        $rawLabel = self::pluralize($days, 'day');
        $humanLabel = self::daysToHuman($days);

        if ($humanLabel === $rawLabel) {
            return $rawLabel;
        }

        return "{$rawLabel} ({$humanLabel})";
    }

    public static function daysToHuman(int $days): string
    {
        $days = max(0, $days);

        $years = intdiv($days, 365);
        $remainingDays = $days % 365;
        $months = intdiv($remainingDays, 30);
        $remainingDays = $remainingDays % 30;

        $parts = [];

        if ($years > 0) {
            $parts[] = self::pluralize($years, 'yr');
        }

        if ($months > 0) {
            $parts[] = self::pluralize($months, 'mo');
        }

        if ($remainingDays > 0 || empty($parts)) {
            $parts[] = self::pluralize($remainingDays, 'day');
        }

        return implode(' ', $parts);
    }

    public static function yearsMonthsToHuman(float|int $years, int $months = 0): string
    {
        $totalMonths = (int) round(((float) $years * 12) + $months);
        $yearsPart = intdiv($totalMonths, 12);
        $monthsPart = $totalMonths % 12;

        $parts = [];

        if ($yearsPart > 0) {
            $parts[] = self::pluralize($yearsPart, 'yr');
        }

        if ($monthsPart > 0 || empty($parts)) {
            $parts[] = self::pluralize($monthsPart, 'mo');
        }

        return implode(' ', $parts);
    }

    protected static function pluralize(int $value, string $unit): string
    {
        $suffix = $value === 1 ? '' : 's';

        return "{$value} {$unit}{$suffix}";
    }
}