<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AdminController extends Controller
{

    private $limit = 12;

    public function statistics(Request $request){

        $page = $request->page ?? 1;

        $statistics = DB::table('orders')
            ->selectRaw("CONCAT(DATE_FORMAT(ordered, '%m'), '.', YEAR(ordered), '.') as 'month', COUNT(ordered) as 'ordered', COUNT(payment) as 'payment', SUM(IF(payment IS NOT NULL, IFNULL(price, 0) * IFNULL(quantity, 0) - IFNULL(expense, 0) * IFNULL(quantity, 0), 0)) as 'revenue'")
            ->groupByRaw("YEAR(ordered), MONTH(ordered)");

        $count = $statistics->count();

        $statistics = $statistics->orderByRaw("YEAR(ordered) DESC, MONTH(ordered) DESC")->offset($page - 1 * $this->limit)->limit($this->limit)->get();

        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $statistics,
            'pagination' => [
                'count' => $count,
                'page' => $page,
                'totalPages' => ceil($count / $this->limit)
            ]
        ], 200);
    }

    public function getAllConfigs(){
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => Config::all()
        ], 200);
    }

    public function getConfig($templateId){
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => Config::where('template_id', $templateId)->first()
        ], 200);
    }

    public function patchConfig(Request $request){
        $validation = Validator::make(
            $request->all(),
            [
                'price' => 'required|numeric',
                'delivery' => 'required|numeric',
                'expense' => 'required|numeric',
                'template_id' => 'required|exists:templates,id'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => 'Podaci su neispravni.',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $config = Config::where('template_id', $request->template_id)->first();
            $config->update($request->only('price', 'delivery', 'expense'));

            return response()->json([
                'status' => true,
                'messages' => 'Uspešna izmena.',
            ], 200);
        } catch(Throwable $e){
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }

        

       
    }
}
