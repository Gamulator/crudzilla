<?php

namespace App\Http\Controllers\API;

use App\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO pagination

        return Currency::orderBy('name')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'name' => 'required',
            'nominal' => 'required|integer',
        ]);

        return Currency::create([
            'code' => $request['code'],
            'name' => $request['name'],
            'nominal' => $request['nominal'],
            'description' => (string)$request['description'],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $code
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        try {
            // не делаем через http_buil_query, чтобы избежать urlencoding слешей в датах, иначе сервис не поймет
            $parameters = [
                'date_req1=' . (new \DateTime())->modify('-1 month')->format('d/m/Y'),
                'date_req2=' . (new \DateTime())->format('d/m/Y'),
                'VAL_NM_RQ=' . urlencode($code) // R01235
            ];

            $response = Http::get('http://www.cbr.ru/scripts/XML_dynamic.asp?' . join('&', $parameters));

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ошибка при загрузке списка валют.'
                ]);
            }

            $rates = [];
            $xml = simplexml_load_string($response->body());

            if ($xml->count()) {
                foreach ($xml->Record as $record) {
                    $rates[] = [
                        'date' => (string)$record->attributes()['Date'],
                        'value' => (float)str_replace(',', '.', $record->Value)
                    ];
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'dates' => array_column($rates, 'date'),
                'values' => array_column($rates, 'value'),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'nominal' => 'required|integer',
            'description' => 'max:255',
        ]);

        $currency = Currency::findOrFail($id);

        $currency->update([
            'name' => $request['name'],
            'nominal' => $request['nominal'],
            'description' => (string)$request['description'],
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Валюта сохранена'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();
        return response()->json([
            'status' => 'ok',
            'message' => 'Валюта удалена из справочника'
        ]);
    }
}
