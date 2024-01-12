<?php
namespace App\Command;

use App\Services\VimeoService;
use App\Services\WordPressService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
 
class RemoveOldPostsCommand extends Command
{
    protected static $defaultName = "run:remove-old-posts";
    
    public function __construct(
        WordPressService $wordPressService,
        VimeoService $vimeoService,
        string $wpAdminUser,
        string $wpAdminPass,
        string $wpPostTitle)
    {
        parent::__construct();

        $this->wordPressService = $wordPressService;
        $this->vimeoService = $vimeoService;
        $this->wpAdminUser = $wpAdminUser;
        $this->wpAdminPass = $wpAdminPass;
        $this->wpPostTitle = $wpPostTitle;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try{
            $now = new \DateTime;
            $interval = new \DateInterval('P1W');
            $last_week = $now->sub($interval);
            $last_week_datetime = $last_week->format('Y-m-d') . "T00:00:00";

            $response = $this->wordPressService->login($this->wpAdminUser, $this->wpAdminPass, false);
            
            if(array_key_exists("error", $response)){
                throw new \Exception($response["error"]);
            }

            $posts = $this->wordPressService->getPostsBeforeTime(
                $last_week_datetime,
                $response["token"]);
            
            $postsToRemove = [];

            forEach($posts as $post){
                $slug = str_replace(" ", "-", strtolower($this->wpPostTitle));
                if(strpos($post["slug"], $slug) !== false){
                    $postsToRemove[] = $post;
                }
            }

            forEach($postsToRemove as $post){
                $this->wordPressService->removePost($post, $response["token"]);
                //$this->vimeoService->removeVideo();
            }

            return 0;
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}