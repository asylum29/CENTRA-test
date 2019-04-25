<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\TagRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    const EVENTS_PER_PAGE = 20;

    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(Request $request, TagRepository $tagRepository, EventRepository $eventRepository): Response
    {
        $page = $request->get('page', 1);
        $tagId = $request->get('tag', 0);

        $queryBuilder = $eventRepository
            ->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC');

        $tag = empty($tagId) ? null : $tagRepository->find($tagId);
        $query = empty($tag) ?
            $queryBuilder->getQuery() :
            $queryBuilder
                ->join('e.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $tagId)
                ->getQuery();

        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::EVENTS_PER_PAGE);

        $page = $page > $pager->getNbPages() ? $pager->getNbPages() : $page;

        $pager->setCurrentPage($page);
        $query
            ->setFirstResult(($page - 1) * self::EVENTS_PER_PAGE)
            ->setMaxResults(self::EVENTS_PER_PAGE);

        $returnUrl = $this->generateUrl('event_index', [ 'tag' => $tagId, 'page' => $page ]);

        return $this->render('event/index.html.twig', [
            'events'    => $query->getResult(),
            'pager'     => $pager,
            'tag'       => $tag,
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $returnUrl = $request->get('returnUrl', $this->generateUrl('event_index'));

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreatedAt(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirect($returnUrl);
        }

        return $this->render('event/new.html.twig', [
            'event'     => $event,
            'form'      => $form->createView(),
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        $returnUrl = $request->get('returnUrl', $this->generateUrl('event_index'));
        return $this->redirect($returnUrl);
    }
}
