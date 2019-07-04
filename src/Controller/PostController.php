<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(PostRepository $repo)
    {
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll(),
        ]);
    }

    /**
     * @param Post $post
     * @param ObjectManager $manager
     * @param Request $request
     * @param PostLikeRepository $likeRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route("/post/{id}/like")
     */

    public function like(Post $post, ObjectManager $manager, Request $request, PostLikeRepository $likeRepository){

        $user = $this->getUser();

        if (!$user){
            return $this->json([
                'code' => 403,
                'message' => "Unauthorized"
            ], 403);
        }

        if ($post->isLikeByUser($user)){
            $like = $likeRepository->findOneBy([
                'post' => $post,
                'user' => $user
            ]);

            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'like bien supprime',
                'likes'  => $likeRepository->count(['post' => $post]),
            ], 200);
        }

        $like = new PostLike();
        $like->setPost($post);
        $like->setUser($user);


        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code' => 200,
            'message' => 'like bien ajoute',
            'likes' => $likeRepository->count(['post' => $post]),
        ], 200);
    }
}
