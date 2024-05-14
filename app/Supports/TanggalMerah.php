<?php
namespace App\Supports;

use Illuminate\Support\Carbon;

class TanggalMerah extends ATanggalMerah{
    public static ATanggalMerah $t;
    public static ?array $tanggalMerahDates = null;
    public static function getLiburDates(int $year = null){
        if(self::$tanggalMerahDates) return self::$tanggalMerahDates;
        self::$t ??= new ATanggalMerah();
        $res = collect(self::$t->getData())->keys()->flatten()->toArray();
        unset($res[count($res)-1]);
        self::$tanggalMerahDates = collect(array_merge($res,self::getDatesByDayName(now(),now()->addYear(1),Carbon::SATURDAY),self::getDatesByDayName(now(),now()->addYear(1),Carbon::SUNDAY)))
            ->unique()->flatten()->toArray();
        return self::$tanggalMerahDates;
    }

    public static function getDatesByDayName(Carbon $fromDate, Carbon $toDate, int $dayId = Carbon::SUNDAY){
        $res = [];
        $startDate = $fromDate->next($dayId); // Get the first friday.
        $endDate = $toDate;
        for ($date = $startDate; $date->lte($endDate); $date->addWeek()) {
            $res[] = $date->format('Y-m-d');
        }
        return $res;
    }
}
