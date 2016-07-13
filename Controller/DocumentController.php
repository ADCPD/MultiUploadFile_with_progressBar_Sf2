<?php

namespace ProgressAssetBundle\Controller;

use AssetBundle\Entity\Asset;
use AssetBundle\Entity\Files;
use ProgressAssetBundle\Form\DocumentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Document controller.
 *
 */
class DocumentController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

//        $documents = $em->getRepository('ProgressAssetBundle:Document')->findAll();
        $documents = $em->getRepository('AssetBundle:Asset')->findAssetFile();

        return $this->render('document/index.html.twig', array(
            'documents' => $documents,
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $document = new Asset();
        $form = $this->createForm(new DocumentType(), $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($document);

            //Persist Files
            $document->getFiles()->map(function (Files $f) use ($document, $em) {

                $f->setAsset($document);
                $f->upload();
                if (!empty($f->getPath())) {
                    $str = $f->getPath();
                    $strTo = explode('.', $str);
                    $name = $strTo[0];
                    $f->setName($name);
                }
                $em->persist($f);
            });

            $em->flush();
            $this->get("session")->getFlashBag()->add('success', "Vos données n'ont pas été correctement enregistrées.");

            return $this->redirectToRoute('document_uploaded_show', array('id' => $document->getId()));
        }

        return $this->render('document/new.html.twig', array(
            'document' => $document,
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Asset $document
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Asset $document)
    {
        $deleteForm = $this->createDeleteForm($document);

        return $this->render('document/show.html.twig', array(
            'document' => $document,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param Asset $document
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Asset $document)
    {
        $deleteForm = $this->createDeleteForm($document);
        $editForm = $this->createForm('ProgressAssetBundle\Form\DocumentType', $document);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($document);
            $em->flush();

            return $this->redirectToRoute('document_uploaded_edit', array('id' => $document->getId()));
        }

        return $this->render('document/edit.html.twig', array(
            'document' => $document,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param Asset $document
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Asset $document)
    {

        $form = $this->createDeleteForm($document);

        $form->handleRequest($request);


        if (($form->isSubmitted() && $form->isValid())) {


            $em = $this->getDoctrine()->getManager();


            $em->remove($document);
            $em->flush();
        }

        return $this->redirectToRoute('document_uploaded_index');
    }

    /**
     * Create the delete form
     * @param Asset $document
     * @return \Symfony\Component\Form\Form
     */
    private
    function createDeleteForm(Asset $document)
    {

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('document_uploaded_delete', array('id' => $document->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
