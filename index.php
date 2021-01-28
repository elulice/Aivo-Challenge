<?php

use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use GuzzleHttp\Exception\RequestException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;

// Credenciales Spotify
define('SPOTIFY_API_CLIENT_ID', 'f50aa7af45e94ada9adebfbaddaeb66f');
define('SPOTIFY_API_CLIENT_SECRET', 'f805d1a7298f4dac80576c2ea2ed74ef');

$app->get('/api/v1/albums', function (Request $request, Response $response, array $args) {
    try {
        if (empty($_GET['q'])) {
            throw new Exception('No hay resultados');
        }
        $query = $_GET['q'];
    } catch (Exception $e) {
        $responseApi = [
            'status' => 'failed',
            'error' => $e->getMessage()
        ];
        return $response->withJson($responseApi);
    }


    // Token de Spotify
    try {
        $cl = new Client;

        $responseToken = $cl->request('POST', 'https://accounts.spotify.com/api/token', [
            'form_params' => ["grant_type" => "client_credentials"],
            'headers' => ['Authorization' => 'Basic ' . base64_encode(SPOTIFY_API_CLIENT_ID . ':' . SPOTIFY_API_CLIENT_SECRET)]
                ]
        );
        $responseToken = json_decode($responseToken->getBody());
        $token = $responseToken->access_token;
    } catch (RequestException $exception) {
        $responseApi = [
            'status' => 'failed',
            'error' => $exception->getMessage()
        ];
        return $response->withJson($responseApi);
    }

    try {
        $cl = new Client;
        $respArt = $cl->request('GET', 'https://api.spotify.com/v1/search?q=' . $query . '&type=artist', [
            'headers' => ['Authorization' => 'Bearer  ' . $token]
                ]
        );
        $respArt = json_decode($respArt->getBody());
    } catch (RequestException $exception) {
        $responseApi = [
            'status' => 'failed',
            'error' => $exception->getMessage()
        ];
        return $response->withJson($responseApi);
    }

    $artistas = $respArt->artists;

    $responseApi = [];

    // Traigo todos los albumes de/del artista/s de la query anterior
    foreach ($artistas->items as $artista) {
        try {
            $cl = new Client;
            $respAlbums = $cl->request('GET', 'https://api.spotify.com/v1/artists/' . $artista->id . '/albums', [
                'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json', 'Content-Type' => 'application/json']
                    ]
            );
            $respAlbums = json_decode($respAlbums->getBody());
            foreach ($respAlbums->items as $item) {
                $responseApi[] = [
                    'name' => $item->name,
                    'released' => date('d-m-Y', strtotime($item->release_date)),
                    'tracks' => $item->total_tracks,
                    'cover' => [
                        'height' => $item->images{0}->height,
                        'width' => $item->images{0}->width,
                        'url' => $item->images{0}->url,
                    ]
                ];
            }
        } catch (RequestException $exception) {
            $responseApi = [
                'status' => 'failed',
                'error' => $exception->getMessage()
            ];
            return $response->withJson($responseApi);
        }
    }

    return $response->withJson([
                'status' => 'ok',
                'albums' => $responseApi
    ]);
});


$app->run();
