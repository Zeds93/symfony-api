<?php

namespace App\Controller;


use App\Entity\Book;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiBookController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/api/books", name="api_book_index", methods={"GET"})
     */
    public function index(BookRepository $bookRepository)
    {

        return $this->json(['hydra:member'=>$bookRepository->findAll(), 'hydra:totalItems'=>count($bookRepository->findAll())], 200, []);

    }



    /**
     * @Route("/api/book/{id}", name="api_book_get", methods={"GET"})
     */
    public function getBookById(BookRepository $bookRepository,$id)
    {
       
        $book = $bookRepository->find($id);

        return $this->json($book, 200, []);
    }


    /**
     * @Route("/api/books", name="api_book_create", methods={"POST"})
     */
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $json = $request->getContent();

        $book = $serializer->deserialize($json, Book::class, 'json');


        $search_book = $this->entityManager->getRepository(Book::class)->findOneByTitle($book->getTitle());

        if (!$search_book){

            foreach ($book->getAuthors() as $author) {

                $bookscount = $author->getBooksCount();
    
                $author->setBooksCount($bookscount+1);
    
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->json($book, 201, []);

            }
        }

    /**
     * @Route("/api/book/{id}", name="api_book_update", methods={"PUT"})
     */

    public function updateBook(Request $request,  $id, SerializerInterface $serializer, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        //Oggetto corrispondente
        $book =  $entityManager->getRepository(Book::class)->findOneById($id);  
        $email = new Mail();


        //Se lil libro non é stato trovato
        if (!$book) {

            return $this->json([
                'statut' => 500,
                'message' => "Id non trovato"
            ],400);

        }

        $json = $request->getContent();
        $bookObj = $serializer->deserialize($json, Book::class, 'json');


        switch ($bookObj) {


            //Caso 1: Titolo si, Autori no = titolo modificato in DB, eliminazione autori associati dal libro
            case count($bookObj->getAuthors()) == 0:

                $book->setTitle($bookObj->getTitle());
                       
                $authors = $book->getAuthors();

                foreach ($authors as $author){

                    $book->removeAuthor($author);
                    
                    $bookscount = $author->getBooksCount();

                    //Se il bookscount dell'autore è > 0 allora si procede alla diminuzione 
                    if ($bookscount > 0) {

                        $author->setBooksCount($bookscount-1);

                    }

                    $entityManager->flush();

                    //Invio mail di conferma
                    $to = $author->getEmail();
                    $email->sendEmail($mailer, $to, "Titolo modificato e autori eliminati", "Le modifiche sono state effettuate");
                }

                break;



            //Caso 2: Titolo si, Autori si = titolo modificato in DB e aggiunta autori al libro
            case $bookObj->getTitle() == !null && count($bookObj->getAuthors()) != 0:        

                $book->setTitle($bookObj->getTitle());

                $authors = $bookObj->getAuthors();

                foreach ($authors as $author){

                    $book->addAuthor($author);


                    $bookscount = $author->getBooksCount();
                    $author->setBooksCount($bookscount+1);

                    $entityManager->flush();
                    
                    $to = $author->getEmail();
                    $email->sendEmail($mailer, $to, "Titolo modificato e autori aggiunti", "Le modifiche sono state effettuate");
                }

                break;

        }

        return $this->json($book, 201, []);

    }
}
