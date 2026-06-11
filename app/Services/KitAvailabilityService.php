class KitAvailabilityService
{
    public function isKitAvailable($kitId, $startDate, $endDate): bool
    {
        $stockTotal = \App\Models\KitUnity::where('kit_id', $kitId)
            ->where('kit_unity_state_id', 1)
            ->count();

        $reservas = DB::table('kit_reserve')
            ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
            ->where('kit_reserve.kit_id', $kitId)
            ->whereIn('reserves.reserve_state_id', [1, 2, 7])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('reserves.start_date', [$startDate, $endDate])
                    ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('reserves.start_date', '<=', $startDate)
                          ->where('reserves.end_date', '>=', $endDate);
                    });
            })
            ->select('reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity')
            ->get();

        $ocupacaoPorDia = [];

        foreach ($reservas as $r) {

            $inicio = \Carbon\Carbon::parse($r->start_date);
            $fim = \Carbon\Carbon::parse($r->end_date);

            while ($inicio <= $fim) {

                $dia = $inicio->format('Y-m-d');

                $ocupacaoPorDia[$dia] = ($ocupacaoPorDia[$dia] ?? 0) + $r->quantity;

                $inicio->addDay();
            }
        }

        foreach ($ocupacaoPorDia as $ocupados) {
            if ($ocupados >= $stockTotal) {
                return false;
            }
        }

        return true;
    }
}