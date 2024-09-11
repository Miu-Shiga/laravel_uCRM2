<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFMService
{
    public static function rfm($subQuery, $rfmPrms)
    {
        //RFM分析
        //1.購買ID毎にまとめる
        $subQuery = $subQuery->groupBy('id')
            ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

        //2.会員毎にまとめ、最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, customer_name,
                MAX(created_at) as recentDate,
                DATEDIFF(NOW(), MAX(created_at)) as recency,
                COUNT(customer_id) as frequency,
                SUM(totalPerPurchase) as monetary');

        //4.会員毎のRFMランクを計算
        $subQuery = DB::table($subQuery)
            ->selectRaw('customer_id, customer_name,
                recentDate, recency, frequency, monetary,
                CASE
                    WHEN recency < ? THEN 5
                    WHEN recency < ? THEN 4
                    WHEN recency < ? THEN 3
                    WHEN recency < ? THEN 2
                    ELSE 1 END as r,
                CASE
                    WHEN ? <= frequency THEN 5
                    WHEN ? <= frequency THEN 4
                    WHEN ? <= frequency THEN 3
                    WHEN ? <= frequency THEN 2
                    ELSE 1 END as f,
                CASE
                    WHEN ? <= monetary THEN 5
                    WHEN ? <= monetary THEN 4
                    WHEN ? <= monetary THEN 3
                    WHEN ? <= monetary THEN 2
                    ELSE 1 END as m', $rfmPrms);

        Log::debug($subQuery->get());

        //5.ランク毎の数を計算
        $totals = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('r, COUNT(r) as count')
            ->orderBy('r', 'desc')
            ->pluck('count');

            Log::debug($rCount);

        $fCount = DB::table($subQuery)
            ->groupBy('f')
            ->selectRaw('f, COUNT(f) as count')
            ->orderBy('f', 'desc')
            ->pluck('count');

        $mCount = DB::table($subQuery)
            ->groupBy('m')
            ->selectRaw('m, COUNT(m) as count')
            ->orderBy('m', 'desc')
            ->pluck('count');

        $eachCount = [];
        $rank = 5;

        for ($i = 0; $i < 5; $i++) {
            array_push($eachCount, [
                'rank' => $rank,
                'r' => $rCount[$i] ?? 0,
                'f' => $fCount[$i] ?? 0,
                'm' => $mCount[$i] ?? 0,
            ]);
            $rank--;
        }

        //6.RとFで2次元表示
        $data = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('CONCAT("r_", r) as rRank,
                COUNT(CASE WHEN f = 5 THEN 1 END) as f_5,
                COUNT(CASE WHEN f = 4 THEN 1 END) as f_4,
                COUNT(CASE WHEN f = 3 THEN 1 END) as f_3,
                COUNT(CASE WHEN f = 2 THEN 1 END) as f_2,
                COUNT(CASE WHEN f = 1 THEN 1 END) as f_1')
            ->orderBy('rRank', 'desc')
            ->get();

        return [$data, $totals, $eachCount];
    }
}
