<?php

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public function __construct(readonly ReductionService $reductionService){}

    /**
     * @return Response
     * @\Symfony\Component\Routing\Annotation\Route("/reduction")
     */
    public function askReduction(): Response
    {
        // récupération via POST de contenu de la requête en JSON
//        $jsonContent =
        // extraction des informations
//        $content = json_decode($jsonContent);
        $arguments = [];
        $promocodeName = '';
        // appel du service pour savoir si la demande est OK ou non
        $this->reductionService->reductionAskAnswer($arguments, $promocodeName);
        // envoi du retour du service
        return new Response();
    }
}
