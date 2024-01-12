<?php
namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Services\TenantService;
use Psr\Log\LoggerInterface;

class WordPressService
{
    private $httpClient;
    
    private $environment;

    public function __construct(
        HttpClientInterface $httpClient, 
        string $wpPostTitle, 
        string $wpPostCategory,
        string $wpAuthURL,
        string $wpRestURL,
        TenantService $tenantService,
        LoggerInterface $padbolLogger)
    {
        $this->httpClient = $httpClient;
        $this->wpPostTitle = $wpPostTitle;
        $this->wpPostCategory = $wpPostCategory;
        $this->wpAuthURL = $wpAuthURL;
        $this->wpRestURL = $wpRestURL;
        $this->tenantService = $tenantService;
        $this->logger = $padbolLogger;
    }

    public function login($username, $password, $shouldHave = true): array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                $this->wpAuthURL . '/token', [
                    'body' => [
                        "username" => $username,
                        "password" => $password
                    ]
                ]
            );

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'
            $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'
            $content = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

            $tenant = $this->tenantService->tenantForUserEmail($content["user_email"]);

            if($shouldHave){
                if(count($tenant) == 0){
                    throw new \Exception("Not valid user with tenant");
                }

                $this->logger->info("Success Login", ["username" => $username, "tenant" => $tenant[0]->getDomain()]);

                $content["user_id"] = $tenant[0]->getUser()->getId();
            }

            return $content;
        } catch (\Exception $e) {
            $this->logger->info("Error Login", ["error" => $e->getMessage()]);
            return ["error" => $e->getMessage()];
        }
    }

    
    public function post($email, $url, $token, $title){
        /*
        <iframe src="https://player.vimeo.com/video/584230042" 
            width="640" height="564" 
            frameborder="0" allow="autoplay; fullscreen" allowfullscreen>
        </iframe>
        */

        $tagId = $this->getTagIdForTentant($email, $token);

        try {
            //$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvcGFkYm9sLm9yZyIsImlhdCI6MTYyODMzODg0MCwibmJmIjoxNjI4MzM4ODQwLCJleHAiOjE2Mjg5NDM2NDAsImRhdGEiOnsidXNlciI6eyJpZCI6IjEifX19.LSn8Bc72bgHFsDjZ9DfGIbGsCWvpMhqA-NerndMhTn0";
            //$url = "/video/584230042";
            //$iframe = '<!-- wp:html --><iframe src="https://player.vimeo.com{{url}}" width="640" height="564" frameborder="0" allow="autoplay; fullscreen" allowfullscreen> </iframe><!-- /wp:html -->';
            $iframe = '
            <p></p>
            <!-- wp:html -->
            <iframe src="https://player.vimeo.com{{url}}" width="640" height="564" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
            <!-- /wp:html -->
            <!-- wp:paragraph -->
            <p></p>
            <!-- /wp:paragraph -->';

            $html = str_replace("{{url}}", $url, $iframe);
            
            $response = $this->httpClient->request(
                'POST',
                $this->wpRestURL . '/posts', [
                    'body' => [
                        "title" => $this->wpPostTitle . " - " . $title,
                        "content" => $html,
                        "status" => "publish",
                        "categories" => [$this->wpPostCategory],
                        "tags" => [$tagId]
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ]
                ]
            );

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'
            $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'
            $content = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

            $this->logger->info("Success Post", ["url" => $url, "tagId" => $tagId]);

            return $content;
        } catch (\Exception $e) {
            $this->logger->info("Error Post", ["error" => $e->getTraceAsString(), "url" => $url]);

            return ["error" => $e->getMessage(), "token" => $token, "url" => $url];
        }
    }

    public function getTagIdForTentant($email, $token){
        try {
            $tenant = $this->tenantService->tenantForUserEmail($email);

            if(count($tenant) == 0){
                throw new \Exception("Not valid user with tenant");
            }

            $response = $this->httpClient->request(
                'GET',
                $this->wpRestURL . '/tags', [
                    'query' => [
                        //'slug' => $tenant[0]->getDomain()
                        'search' => $tenant[0]->getDomain()
                    ],
                    'body' => [],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ]
                ]
            );

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'
            $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'
            $content = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

            if(count($content) == 0){
                throw new \Exception("Not valid tag name");
            }

            return $content[0]["id"];
            
        } catch (\Exception $e) {
            $this->logger->error("Error Getting TagId", [
                "error" => $e->getMessage(), 
                "email" => $email]);
            
            return ["error" => $e->getMessage()];
        }
    }

    public function getPostsBeforeTime($dateTime, $token){
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->wpRestURL . '/posts', [
                    'query' => [
                        'before' => $dateTime,
                        'per_page' => 100,
                        'search' => $this->wpPostTitle
                    ],
                    'body' => [],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ]
                ]
            );

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'
            $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'
            $content = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

            return $content;
            
        } catch (\Exception $e) {
            $this->logger->error("Error Getting Posts from time before", [
                "error" => $e->getMessage()
            ]);
            
            return ["error" => $e->getMessage()];
        }
    }

    public function removePost($post, $token){
        try {
            
            $response = $this->httpClient->request(
                'DELETE',
                $this->wpRestURL . '/posts/' . $post["id"], [
                    'body' => [],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ]
                ]
            );

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'
            $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'
            $content = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

            return $content;
            
        } catch (\Exception $e) {
            $this->logger->error("Error Deleting Post", [
                "error" => $e->getMessage()
            ]);
            
            return ["error" => $e->getMessage()];
        }
    }
}