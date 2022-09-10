<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\OrganizationType;
use App\Model\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organizations_")
 */
class OrganizationController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(OrganizationRepository $organizationRepository): Response  {
        return $this->render('organization/index.html.twig', [
            'organizations' => $organizationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/organizations/new", name="new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        OrganizationRepository $organizationRepository
    ): Response {
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organizationRepository->persist($organization);

            $this->addFlash('success', 'Organization added successfully');

            return $this->redirectToRoute('app_organizations_index');
        }

        return $this->render(
            'organization/edit.html.twig',
            [
                'form' => $form->createView(),
                'edit' => false,
            ]
        );
    }

    /**
     * @Route("/organizations/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        OrganizationRepository $organizationRepository
    ): Response {
        $name = $request->query->get('name');
        if (
            null === $name || '' === $name
        ) {
            throw new BadRequestHttpException('Name required in the request query');
        }

        $organization = $organizationRepository->findOneByName($name);

        if (null === $organization) {
            $this->addFlash('danger', sprintf('"%s" organization not found', $name));

            return $this->redirectToRoute('app_organizations_index');
        }

        $form = $this->createForm(
            OrganizationType::class,
            $organization,
            ['edit' => true]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $organizationRepository->updateByName($name, $organization);

            $this->addFlash('success', "Organization '{$name}' edited successfully");

            return $this->redirectToRoute('app_organizations_index');
        }

        return $this->render(
            'organization/edit.html.twig',
            [
                'form' => $form->createView(),
                'edit' => true,
            ]
        );
    }

    /**
     * @Route("/organizations/delete", name="delete", methods={"GET"})
     */
    public function delete(
        Request $request,
        OrganizationRepository $organizationRepository
    ): RedirectResponse {
        $name = $request->query->get('name');
        if (
            null === $name || '' === $name
        ) {
            throw new BadRequestHttpException('Name required in the request query');
        }

        $organizationRepository->removeByName($name);

        $this->addFlash('success', "Organization '{$name}' deleted successfully");

        return $this->redirectToRoute('app_organizations_index');
    }
}
