<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\File;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('pt_BR');

        $data = [
            "idevento" => 1,
            "evento" => "melhorando apresentação da data",
            "data" => "21/10/2024",
            "hora" => "15:30:00",
            "contato" => "joao",
            "endereco" => "teste",
            "workers" => []
        ];

        // Gerar 1000 workers
        for ($i = 0; $i < 700; $i++) {
            $data['workers'][] = [
                "id" => strtoupper($faker->bothify('####-????-####')),
                "nome" => $faker->name,
                // Gerar telefone no formato +55dddnumeros
                "telefone" => '+55' . $faker->numerify('##') . $faker->numerify('#########'),
                "valor" => $faker->numberBetween(50, 200)
            ];
        }

        // Salvar em um arquivo JSON na pasta storage
        $filePath = storage_path('app/workers.json');
        File::put($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->command->info("Arquivo 'workers.json' gerado com sucesso em: $filePath");
    }
}
