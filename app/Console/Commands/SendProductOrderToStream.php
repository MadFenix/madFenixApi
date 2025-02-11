<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Store\Domain\ProductOrder;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendProductOrderToStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-product-order-stream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send product order to stream';


// 1️⃣ FUNCIÓN PARA OBTENER EL ACCESS TOKEN
    function obtenerAccessToken($client_id, $client_secret)
    {
        $url = "https://streamlabs.com/api/v2.0/token";
        $data = [
            "grant_type" => "authorization_code",
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "redirect_uri" => "https://madfenix.com",
            "code" => ""
        ];

        // Inicializar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Ejecutar la petición
        $response = curl_exec($ch);
        curl_close($ch);
var_dump($response);
        // Decodificar respuesta JSON
        $resultado = json_decode($response, true);

        // Verificar si se obtuvo el access_token
        if (isset($resultado["access_token"])) {
            return $resultado["access_token"];
        } else {
            die("Error al obtener el access_token: " . json_encode($resultado));
        }
    }

    function enviarAlertaStreamlabs($mensaje, $imagen_url = "", $sonido_url = "", $tipo = "donation", $duracion = 5) {
        /*$access_token = $this->obtenerAccessToken(
            '',
            '');*/
        $access_token = '';

        $url = "https://streamlabs.com/api/v2.0/alerts";
        $data = [
            "type" => $tipo, // Puede ser "follow", "subscription", "donation", etc.
            "message" => $mensaje,
            "image_href" => $imagen_url,
            "sound_href" => $sonido_url,
            "duration" => $duracion
        ];

        // Inicializa cURL
        $ch = curl_init();

        // Configuración de la petición
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $access_token,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Ejecuta la petición
        $response = curl_exec($ch);
var_dump($response);
        // Cierra la conexión cURL
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 	9e2e14a0-d08c-4682-8b63-ab9fc93f8c3e
        // 3Q568GwNMtySDpUeraxEUTam9ZyTgb9h8uBAzKiq
        $productOrders = ProductOrder::orderBy('id', 'desc')
            ->limit(5)
            ->get();

        foreach ($productOrders as $productOrder) {
            $nowMinus30sec = Carbon::now();
            $nowMinus30sec->subtract('1 minute');
            if ($productOrder->created_at > $nowMinus30sec) {
                $mensaje = $productOrder->product->name . ' ' . $productOrder->user->name;
                echo $mensaje;
                $respuesta = $this->enviarAlertaStreamlabs(
                    $mensaje,
                    "",
                    "",
                    "donation",
                    5
                );
                var_dump($respuesta);
            }
        }
    }
}
