<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Création de l'email
            $email = (new Email())
                ->from($contact->getEmail())
                ->to('ecrituredesoie@gmail.com')
                ->subject('Nouveau message de contact : ' . $contact->getSubject())
                ->html(
                    '<h2>Nouveau message de contact</h2>' .
                    '<p><strong>Nom :</strong> ' . $contact->getName() . '</p>' .
                    '<p><strong>Email :</strong> ' . $contact->getEmail() . '</p>' .
                    '<p><strong>Téléphone :</strong> ' . $contact->getTelephone() . '</p>' .
                    '<p><strong>Sujet :</strong> ' . $contact->getSubject() . '</p>' .
                    '<p><strong>Message :</strong></p>' .
                    '<p>' . nl2br(htmlspecialchars($contact->getMessage())) . '</p>'
                );

            try {
                // Envoi de l'email
                $mailer->send($email);

                // Message de confirmation
                $this->addFlash('success', 'Votre message a été envoyé avec succès !');

                // Redirection pour éviter la re-soumission
                return $this->redirectToRoute('app_contact');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.');
            }
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
