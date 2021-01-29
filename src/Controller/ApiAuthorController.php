<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiAuthorController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/author", name="api_author_index", methods={"GET"})
     */
    public function index(AuthorRepository $authorRepository): Response
    {
        return $this->json(['hydra:member' => $authorRepository->findAll(), 'hydra:totalItems' => count($authorRepository->findAll())], 200, [], ['groups' => 'author:read']);
    }

    /**
     * @Route("/api/author/{id}", name="api_author_get", methods={"GET"})
     */
    public function getAuthorById(AuthorRepository $authorRepository, $id): Response
    {

        return $this->json($authorRepository->findById($id), 200, [], ['groups' => 'author:read']);

    }

    /**
     * @Route("/api/authors", name="api_author_create", methods={"POST"})
     */
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {

        $json = $request->getContent();

        try {

            $author = $serializer->deserialize($json, Author::class, 'json');
            $author->setBooksCount(0);
    
            $checkAuthorExist = $this->entityManager->getRepository(Author::class)->findOneBy(
                ['name' => $author->getName(),
                 'email'=> $author->getEmail()]
            );    
            
            $errors = $validator->validate($author);

            if($checkAuthorExist || count($errors) > 0) {

                return $this->json([
                    'statut' => 400,
                    'message' => ($errors?: "Errore")
                ],400);

            } else {

                $entityManager->persist($author);
                $entityManager->flush();

            }

            return $this->json($author, 201, []);

        } catch(NotEncodableValueException $e) {

            return $this->json([
                'statut' => 400,
                'message' => $e->getMessage()
            ],400);

        }

    }

    /**
     * @Route("/api/author/{id}", name="api_author_update", methods={"PUT"})
     */
    public function updateAuthor(Request $request,  $id, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $author =  $entityManager->getRepository(Author::class)->findOneById($id);;

        //Se l'autore non é stato trovato
        if (!$author) {

            return $this->json([
                'statut' => 500,
                'message' => "Id non trovato"
            ],400);

        }

        $json = $request->getContent();

        $authorobj = $serializer->deserialize($json, Author::class, 'json');

        //Switch per gestire casistica in caso l'utente voglia modificare un elemento singolo
        switch ($authorobj) {

            case $authorobj->getName() == null:

                $author->setEmail($authorobj->getEmail());

                break;

            case $authorobj->getEmail() == null:

                $author->setName($authorobj->getName());

                break;

            case $authorobj->getEmail() == !null && $authorobj->getName() == !null:
                
                $author->setName($authorobj->getName());
                $author->setEmail($authorobj->getEmail());

                break;

        }

        $entityManager->flush();

        return $this->json($author, 201, []);

    }

}
