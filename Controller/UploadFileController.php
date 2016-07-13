<?php

namespace ProgressAssetBundle\Controller;

use ProgressAssetBundle\Entity\Files;
use ProgressAssetBundle\Form\FilesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UploadFileController
 * @package ProgressAssetBundle\Controller
 */
class UploadFileController extends Controller
{
    /**
     * Files liste
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $uploadFiles = $em->getRepository('ProgressAssetBundle:Files')->findAll();

        return $this->render('uploadfile/index.html.twig', array(
            'uploadFiles' => $uploadFiles,
        ));
    }


    /**
     *  Flattens a given filebag to extract all files.
     *
     * @param FileBag $bag The filebag to use
     * @return array An array of files
     */
    protected function getFiles(FileBag $bag)
    {
        $files = array();
        $fileBag = $bag->all();
        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($fileBag), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($fileIterator as $file) {
            if (is_array($file) || null === $file) {
                continue;
            }
            $files[] = $file;
        }
        return $files;
    }


    /**
     * Validate form with AJAX
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonRequestFormAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $retour = array(
            "error" => true,
            "message" => "Les fichiers sont correctement enregistrées !",
        );
        $files = $this->getFiles($request->files);
        foreach ($files as $index => $file) {
            $document = new Files();
            $document->setFile($file);

            /* uploader le fichier */
            $document->upload();

            /* Recuperer le nom fichier charger en base  */
            if (!empty($document->getPath())) {
                $str = $document->getPath();
                $strTo = explode('.', $str);
                $name = $strTo[0];
                $document->setName($name);

                /* recuperer le size du fichier charger en base  */
                $document->setFileSize(filesize($document->getAbsolutePath()));
            }

            /* recuperer la date d'upload d'un fichier charger en base  */
            $document->setDateUpload($document->init());


            $em->persist($document);
            $em->flush();
        }
        return new JsonResponse($retour);


    }


    /**
     * New file uploaded
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */

    public function newAction(Request $request)
    {
        $file = new Files();
//        $form = $this->createForm(new FilesType(), $file);
        $form = $this->createForm(new FilesType(), $file);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $file->upload();
            $file->setDateUpload($file->init()); //ajouter ressament
            if (!empty($file->getPath())) {

                /* Recuperer le nom fichier charger en base  */
                $str = $file->getPath();
                $strTo = explode('.', $str);
                $name = $strTo[0];
                $file->setName($name);

                /* recuperer la taille d'un fichier charger en base  */

                $file->setFileSize("" . $file->getPath());
            }


            $em->persist($file);


            $em->flush();
            $this->get("session")->getFlashBag()->add('success', "Vos données n'ont pas été correctement enregistrées.");

            return $this->redirectToRoute('uploadfile_index', array('id' => $file->getId()));
        }

        return $this->render('uploadfile/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Files $uploadFile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public
    function showAction(Files $uploadFile)
    {
        $deleteForm = $this->createDeleteForm($uploadFile);

        return $this->render('uploadfile/show.html.twig', array(
            'uploadFile' => $uploadFile,
            'delete_form' => $deleteForm->createView(),
        ));
    }

//    /**
//     * @param Request $request
//     * @param Files $uploadFile
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     */
//    public function editAction(Request $request, Files $uploadFile)
//    {
//        $deleteForm = $this->createDeleteForm($uploadFile);
//        $editForm = $this->createForm(new EditFileType(), $uploadFile);
//        $editForm->handleRequest($request);
//
//        if ($editForm->isSubmitted() && $editForm->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $uploadFile->upload();
//            $uploadFile->setDateUpload($uploadFile->init());
//            if (!empty($uploadFile->getPath())) {
//                $str = $uploadFile->getPath();
//                $strTo = explode('.', $str);
//                $name = $strTo[0];
//                $uploadFile->setName($name);
//            }
//            $em->persist($uploadFile);
//            $em->flush();
//
//            return $this->redirectToRoute('uploadfile_edit', array('id' => $uploadFile->getId()));
//        }
//
//        return $this->render('uploadfile/edit.html.twig', array(
//            'uploadFile' => $uploadFile,
//            'edit_form' => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        ));
//    }

    /**
     * @param Request $request
     * @param Files $uploadFile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function deleteAction(Request $request, Files $uploadFile)
    {
        $form = $this->createDeleteForm($uploadFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($uploadFile);
            $em->flush();
        }

        return $this->redirectToRoute('uploadfile_index');
    }

    /**
     * @param Files $uploadFile
     * @return \Symfony\Component\Form\Form
     */
    private
    function createDeleteForm(Files $uploadFile)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('uploadfile_delete', array('id' => $uploadFile->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
