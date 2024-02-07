<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubdomainController extends Controller
{
    public function createDNSRecord(Request $request)
    {
        $subdomain = preg_replace('/[^a-zA-Z0-9\-_]/', '', $request->input('subdomain'));
        $environment = preg_replace('/[^a-zA-Z0-9\-_]/', '', $request->input('environment'));

        if ($environment == "pro" || $environment == "pre" || $environment == "test") {
            $result = $this->createCloudflareDNSRecord($subdomain, $environment);
            return $result;
            if ($result == "success") {
                return response()->json(['message' => 'Subdominio creado.']);
            } else {
                return response()->json(['error' => $result]);
            }
        } else {
            return response()->json(['error' => 'Ambiente no válido.']);
        }
    }

    private function createCloudflareDNSRecord($subdomain, $environment)
    {
        // Tus credenciales de Cloudflare
        $email = env('EMAIL_CLOUDFLARE');
        $api_key = env('API_KEY_CLOUDFLARE');
        $zone_id = env('ZONE_ID_CLOUDFLARE');
        $ip_address = env('IP_ADDRESS');
        return $email." ".$api_key." ".$zone_id." ".$ip_address." ".
        // Construye el nombre completo del subdominio
        $full_domain = $subdomain . ($environment == "pro" ? '' : '.' . $environment) . '.thehoster.io';

        // Inicializa cURL
        $ch = curl_init("https://api.cloudflare.com/client/v4/zones/{$zone_id}/dns_records");

        // Prepara el payload JSON
        $payload = json_encode([
            'type'    => 'A',
            'name'    => $full_domain,
            'content' => $ip_address,
            'ttl'     => 1,
            'proxied' => false,
        ]);

        // Configura las opciones de cURL
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $api_key,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Ejecuta la petición y guarda la respuesta
        $response = curl_exec($ch);
        curl_close($ch);

        // Verifica si la respuesta es exitosa
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            return "success";
        } else {
            return "Error al crear el subdominio: " . $response;
        }
    }
}
