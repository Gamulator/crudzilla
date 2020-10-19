<?php

namespace App\Console\Commands;

use App\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузка списка валют с cbr.ru и сохранение в базу данных';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $response = Http::get('http://www.cbr.ru/scripts/XML_val.asp');

            if ($response->failed()) {
                echo 'Ошибка при загрузке списка валют.';
                return 1;
            }

            $xml = simplexml_load_string($response->body());

            if ($xml->count()) {
                foreach ($xml->Item as $item) {
                    Currency::updateOrCreate([
                        'code' => $item->attributes()['ID'],
                    ], [
                        'code' => $item->attributes()['ID'],
                        'name' => $item->EngName,
                        'nominal' => $item->Nominal,
                    ]);
                }
            }

        } catch (\Throwable $e) {
            echo 'Ошибка: ' . $e->getMessage();
            return 1;
        }

        return 0;
    }
}
