<?php
if( !class_exists("CMMicropaymentPlatformWalletCharges") )
{
    include_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
}

class CMMicropaymentPlatformBackendReportWalletData extends CMMicropaymentPlatformWalletCharges
{

    public function prepareData()
    {
        $type = (!isset($_GET['view'])) ? 'amount' : $_GET['view'];
        $range = (isset($_GET['range']) ? $_GET['range'] : 'all');
        switch($range)
        {
            case 'yesterday':
            case 'today':
                $data = $this->prepareHourlyData($type, $range);
                break;
            case 'last_year':
            case 'this_year':
                $data = $this->prepareYearlyData($type, $range);
                break;
            case 'last_week':
            case 'this_week':
                $data = $this->prepareWeeklyData($type, $range);
                break;
            case 'last_month':
            case 'this_month':
            default:
                $data = $this->prepareMonthlyData($type, $range);
                break;
        }
        return $data;
    }

    private function prepareHourlyData($type, $month)
    {
        $data = array();

        $startDate = new DateTime('now');

        if( $month == 'yesterday' )
        {
            $startDate = new DateTime('-1 day');
        }

        $startDate->setTime(0, 0, 0);
        $lastDate = clone $startDate;
        $lastDate->setTime(23, 59, 59);

        switch($type)
        {
            default:
            case 'count':
                $dbData = $this->getCountReport('Hour', $startDate->format('Y-m-d H:i'), $lastDate->format('Y-m-d H:i'));
                break;
            case 'amount':
                $dbData = $this->getAmountReport('Hour', $startDate->format('Y-m-d H:i'), $lastDate->format('Y-m-d H:i'));
                break;
            case 'points':
                $dbData = $this->getPointsReport('Hour', $startDate->format('Y-m-d H:i'), $lastDate->format('Y-m-d H:i'));
                break;
        }

        foreach($dbData AS $d)
        {
            $t = new DateTime($d->order_date);

            if( $t->format('Y-m-d') == $startDate->format("Y-m-d") )
            {
                $readyYData[$t->format("Y-m-d H")] = $d->total_amount;
            }
        }

        $totals = 0;

        while($startDate <= $lastDate)
        {
            if( !isset($readyYData[$startDate->format('Y-m-d H')]) )
            {
                $data[] = array($startDate->format('Y-m-d') . "T" . $startDate->format('H') . ":00:00", 0);
            }
            else
            {
                $data[] = array($startDate->format('Y-m-d') . "T" . $startDate->format('H') . ":00:00", $readyYData[$startDate->format('Y-m-d H')]);
                $totals += $readyYData[$startDate->format('Y-m-d H')];
            }

            $startDate->modify('+1 hour');
        }

        return array('data' => $data, 'totals' => $totals);
    }

    private function prepareWeeklyData($type, $month)
    {
        $data = array();

        $startDate = new DateTime('now');

        if( $month == 'last_week' )
        {
            $startDate = new DateTime('-1 week');
        }

        if( $startDate->format('N') != 1 )
        {
            $startDate->modify('last monday');
        }

        $startDate->setTime(0, 0, 0);

        $totals = 0;


        $lastDate = clone $startDate;
        $lastDate->modify('next friday');

        switch($type)
        {
            default:
            case 'count':
                $dbData = $this->getCountReport('date', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
            case 'amount':
                $dbData = $this->getAmountReport('date', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
            case 'points':
                $dbData = $this->getPointsReport('date', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
        }

        foreach($dbData AS $d)
        {
            $t = new DateTime($d->date);

            if( $t >= $startDate )
            {
                $readyYData[$d->date] = $d->total_amount;
            }
        }
        $totals = 0;
        while($startDate <= $lastDate)
        {
            if( !isset($readyYData[$startDate->format('Y-m-d')]) )
            {
                $data[] = array($startDate->format('Y-m-d'), 0);
            }
            else
            {
                $data[] = array($startDate->format('Y-m-d'), $readyYData[$startDate->format('Y-m-d')]);
                $totals += $readyYData[$startDate->format('Y-m-d')];
            }

            $startDate->modify('+1 day');
        }

        return array('data' => $data, 'totals' => $totals);
    }

    private function prepareMonthlyData($type, $month)
    {
        $data = array();

        $startDate = new DateTime('now');

        if( $month == 'last_month' )
        {
            $startDate = new DateTime('-1 month');
        }

        $startDate->setDate($startDate->format('Y'), $startDate->format('m'), 1);
        $startDate->setTime(0, 0, 0);
        $lastDate = clone $startDate;
        $lastDate->setDate($startDate->format('Y'), $startDate->format('m'), $startDate->format('t'));

        switch($type)
        {
            default:
            case 'count':
                $dbData = $this->getCountReport('date', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
            case 'amount':
                $dbData = $this->getAmountReport('date', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
            case 'points':
                $dbData = $this->getPointsReport('date', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
        }

        $totals = 0;
        foreach($dbData AS $d)
        {
            $t = new DateTime($d->date);

            if( $t >= $startDate )
            {
                $readyYData[$d->date] = $d->total_amount;
            }
        }

        while($startDate <= $lastDate)
        {
            if( !isset($readyYData[$startDate->format('Y-m-d')]) )
            {
                $data[] = array($startDate->format('Y-m-d'), 0);
            }
            else
            {
                $data[] = array($startDate->format('Y-m-d'), $readyYData[$startDate->format('Y-m-d')]);
                $totals += $readyYData[$startDate->format('Y-m-d')];
            }

            $startDate->modify('+1 day');
        }

        return array('data' => $data, 'totals' => $totals);
    }

    private function prepareYearlyData($type, $month)
    {
        $data = array();

        $startDate = new DateTime('now');

        if( $month == 'last_year' )
        {
            $startDate = new DateTime('-1 year');
        }

        $startDate->setDate($startDate->format('Y'), 1, 1);
        $startDate->setTime(0, 0, 0);
        $lastDate = clone $startDate;
        $lastDate->setDate($startDate->format('Y'), 12, $startDate->format('t'));

        switch($type)
        {
            default:
            case 'count':
                $dbData = $this->getCountReport('Year', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
            case 'amount':
                $dbData = $this->getAmountReport('Year', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
            case 'points':
                $dbData = $this->getPointsReport('Year', $startDate->format('Y-m-d'), $lastDate->format('Y-m-d'));
                break;
        }

        $totals = 0;

        foreach($dbData AS $d)
        {
            $t = new DateTime($d->date);

            if( $t >= $startDate )
            {
                $readyYData[$t->format("Y-m")] = $d->total_amount;
            }
        }

        while($startDate <= $lastDate)
        {
            if( !isset($readyYData[$startDate->format('Y-m')]) )
            {
                $data[] = array($startDate->format('Y-m'), 0);
            }
            else
            {
                $data[] = array($startDate->format('Y-m'), $readyYData[$startDate->format('Y-m')]);
                $totals = $totals + (int) $readyYData[$startDate->format('Y-m')];
            }

            $startDate->modify('+1 month');
        }
        return array('data' => $data, 'totals' => $totals);
    }

}