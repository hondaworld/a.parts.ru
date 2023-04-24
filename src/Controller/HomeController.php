<?php

namespace App\Controller;

use App\ReadModel\Manager\NewsAdminFetcher;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(NewsAdminFetcher $newsAdminFetcher, EntityManagerInterface $em)
    {
//                $em = $this->getDoctrine()->getManager();
//        $sql = 'SELECT * FROM managers';
//        $statement = $em->getConnection()->prepare($sql);
//        $statement->execute();
//        $result = $statement->fetchAll();
////
//        dump($result);

//        dump($this->getUser());
//        phpinfo();



        $news = $newsAdminFetcher->active();

        return $this->render('app/home.html.twig', [
            'news' => $news
        ]);
    }
}
