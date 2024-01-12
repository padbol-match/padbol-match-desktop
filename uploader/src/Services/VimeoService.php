<?php
namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\VimeoRepository;
use Vimeo\Vimeo;

class VimeoService
{
    public function __construct(
        string $vimeoToken,
        string $vimeoKey,
        string $vimeoSecret)
    {
        $this->vimeoToken = $vimeoToken;
        $this->vimeoKey = $vimeoKey;
        $this->vimeoSecret = $vimeoSecret;
    }

    public function getCredentials(): array
    {
        return [
            "token" => $this->vimeoToken,
            "key" => $this->vimeoKey,
            "secret" => $this->vimeoSecret
        ];
    }

    public function removeVideo()
    {
        $client = new Vimeo($this->vimeoKey, $this->vimeoSecret, $this->vimeoToken);
        $response = $client->request('/me/videos', [
            'name' => 'Padbol - 2022_09_16_19_00_2-mp4'
        ], 'PATCH');

        dump($response);
    }

}