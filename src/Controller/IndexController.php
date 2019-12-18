<?php

namespace App\Controller;

use App\Entity\Job;
use App\Form\InscriptionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $jobs = $this->getDoctrine()->getRepository(Job::class)->findAll();

//        var_dump($jobs);

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'jobs' => $jobs
        ]);
    }

    /**
     * @Route("jobs/{slug}", methods={"GET"}, name="job")
     */

    public function jobShow(Job $job)
    {
        return $this->render('jobs/job.html.twig', [
            'job' => $job
        ]);
    }

    /**
     * @Route("inscription", name="inscription")
     */

    public function inscriptionForm(Request $request)
    {
        $form = $this->createForm(InscriptionType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile = $form['cv']->getData();

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cvFile->guessExtension();

                dump($newFilename);

                try {
                    $cvFile->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
            }

            return $this->redirectToRoute('index');
        }

        return $this->render('jobs/cv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
