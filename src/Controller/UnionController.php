<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\Union;
use App\Form\PersonSelectType;
use App\Form\UnionType;
use App\Repository\UnionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UnionController extends AbstractController
{
    #[Route('/person/{personId}/union/new', name: 'app_union_new', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'person')]
    public function new(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $union = new Union();
        $form = $this->createForm(UnionType::class, $union);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $person->addUnion($union);
            $entityManager->persist($union);
            $entityManager->flush();

            return $this->redirectToRoute('app_union_edit', [
                'personId' => $person->getId(),
                'id' => $union->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('union/new.html.twig', [
            'union' => $union,
            'form' => $form,
            'person' => $person,
        ]);
    }

    #[Route('/union/{id}', name: 'app_union_show', methods: ['GET'])]
    #[IsGranted('view', 'union')]
    public function show(Union $union): Response
    {
        return $this->render('union/show.html.twig', [
            'union' => $union,
        ]);
    }
    
    #[Route('/person/{personId}/union/{id}/edit', name: 'app_union_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'union')]
    public function edit(Request $request, Union $union, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $form = $this->createForm(UnionType::class, $union);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            return $this->redirectToRoute('app_union_edit', [
                'personId' => $person->getId(),
                'id' => $union->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        $treeMember = $person->getTree()->getMembers()->toArray();
        $unionMembers = [
            ...$union->getPeople()->toArray(),
            ...$union->getChildren()->toArray()
        ];
        
        return $this->render('union/edit.html.twig', [
            'union' => $union,
            'form' => $form,
            'person' => $person,
            'partner_form' => $this->createForm(PersonSelectType::class, null, [
                'available_members' => $treeMember,
                'union_members' => $unionMembers,
            ]),
            'child_form' => $this->createForm(PersonSelectType::class, null, [
                'available_members' => $treeMember,
                'union_members' => $unionMembers,
            ]),
        ]);
    }

    #[Route('/union/{id}', name: 'app_union_delete', methods: ['POST'])]
    #[IsGranted('delete', 'union')]
    public function delete(Request $request, Union $union, EntityManagerInterface $entityManager): Response
    {
        /** @var Tree */
        $tree = $union->getPeople()->first()->getTree();

        if ($this->isCsrfTokenValid('delete'.$union->getId(), $request->request->get('_token'))) {
            // First, remove the union from all people
            foreach ($union->getPeople() as $person) {
                $person->removeUnion($union);
            }
            foreach ($union->getChildren() as $child) {
                $union->removeChild($child);
            }

            $entityManager->remove($union);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tree_show', ['id' => $tree->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{personId}/union/{id}/add-partner', name: 'app_union_add_partner', methods: ['POST'])]
    #[IsGranted('edit', 'union')]
    public function addPartner(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person, Union $union): Response
    {
        $form = $this->createForm(PersonSelectType::class, null, [
            'available_members' => $person->getTree()->getMembers()->toArray(),
            'union_members' => [
                ...$union->getPeople()->toArray(),
                ...$union->getChildren()->toArray()
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $union->addPerson($form->get('person')->getData());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_edit', [
            'personId' => $person->getId(),
            'id' => $union->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{personId}/union/{id}/remove-partner/{partnerId}', name: 'app_union_remove_partner', methods: ['POST'])]
    #[IsGranted('edit', 'union')]
    public function removePartner(
        Request $request, 
        EntityManagerInterface $entityManager, 
        #[MapEntity(id: 'personId')] 
        Person $person, 
        Union $union, 
        #[MapEntity(id: 'partnerId')] 
        Person $partner
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getId(), $request->request->get('_token'))) {
            $union->removePerson($partner);

            if ($union->getPeople()->count() === 0) {
                foreach ($union->getChildren() as $child) {
                    $union->removeChild($child);
                }
                $entityManager->remove($union);
                $entityManager->flush();

                return $this->redirectToRoute('app_person_edit', [
                    'treeId' => $person->getTree()->getId(),
                    'id' => $person->getId(),
                ], Response::HTTP_SEE_OTHER);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_edit', [
            'personId' => $person->getId(),
            'id' => $union->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{personId}/union/{id}/add-child', name: 'app_union_add_child', methods: ['POST'])]
    #[IsGranted('edit', 'union')]
    public function addChild(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person, Union $union): Response
    {
        $form = $this->createForm(PersonSelectType::class, null, [
            'available_members' => $person->getTree()->getMembers()->toArray(),
            'union_members' => [
                ...$union->getPeople()->toArray(),
                ...$union->getChildren()->toArray()
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $union->addChild($form->get('person')->getData());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_edit', [
            'personId' => $person->getId(),
            'id' => $union->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{personId}/union/{id}/remove-child/{childId}', name: 'app_union_remove_child', methods: ['POST'])]
    #[IsGranted('edit', 'union')]
    public function removeChild(
        Request $request, 
        EntityManagerInterface $entityManager, 
        #[MapEntity(id: 'personId')] 
        Person $person, 
        Union $union, 
        #[MapEntity(id: 'childId')] 
        Person $child
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$child->getId(), $request->request->get('_token'))) {
            $union->removeChild($child);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_edit', [
            'personId' => $person->getId(),
            'id' => $union->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
