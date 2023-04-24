<?php

namespace App\ReadModel\Reports;

use DateTime;

class ReportFetcher
{
    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTill
     * @param DateTime $dateofprev
     * @return array
     */
    protected function getPrevDates(DateTime $dateFrom, DateTime $dateTill, DateTime $dateofprev): array
    {
        $days = $dateFrom->diff($dateTill);

        $datePrev = clone($dateofprev);
        $datePrevTill = clone($datePrev);
        $datePrevTill->modify('+' . $days->days . ' day');

        $date = $datePrev;
        while ($date <= $datePrevTill) {
            $result[] = clone($date);
            $date->modify('+1 day');
        }
        return $result;
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTill
     * @return array
     */
    protected function getDates(DateTime $dateFrom, DateTime $dateTill, string $period = 'day'): array
    {
        $result = [];
        $date = $dateFrom;
        while ($date <= $dateTill) {
            $result[] = clone($date);
            $date->modify('+1 ' . $period);
        }
        return $result;
    }
}