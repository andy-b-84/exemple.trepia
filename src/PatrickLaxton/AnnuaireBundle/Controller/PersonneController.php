<?php

namespace PatrickLaxton\AnnuaireBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PatrickLaxton\AnnuaireBundle\Entity\Personne;
use PatrickLaxton\AnnuaireBundle\Form\PersonneType;
use PatrickLaxton\AnnuaireBundle\Form\AnnuaireType;
use PatrickLaxton\AnnuaireBundle\Service\Importer;

/**
 * Personne controller.
 *
 * @Route("/personne")
 */
class PersonneController extends Controller {

    /**
     * Lists all Personne entities.
     *
     * @Route("/", name="personne")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PatrickLaxtonAnnuaireBundle:Personne')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Personne entity.
     *
     * @Route("/", name="personne_create")
     * @Method("POST")
     * @Template("PatrickLaxtonAnnuaireBundle:Personne:new.html.twig")
     */
    public function createAction(Request $request) {
        $entity = new Personne();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('personne_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Personne entity.
     *
     * @param Personne $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Personne $entity) {
        $form = $this->createForm(new PersonneType(), $entity, array(
            'action' => $this->generateUrl('personne_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Personne entity.
     *
     * @Route("/new", name="personne_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction() {
        $entity = new Personne();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to edit a Personne entity.
     *
     * @param Personne $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Personne $entity) {
        $form = $this->createForm(new PersonneType(), $entity, array(
            'action' => $this->generateUrl('personne_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Creates a form to delete a Personne entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('personne_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * Displays a form to import a csv file with Personne entities inside it.
     *
     * @Route("/import", name="personne_import")
     * @Method("GET")
     * @Template("PatrickLaxtonAnnuaireBundle:Personne:import.html.twig")
     */
    public function importAction() {
        $importForm = $this->createImportForm();

        return array(
            'import_form' => $importForm->createView(),
        );
    }

    /**
     * Creates a form to import a csv file with Personne entities inside it.
     * 
     * @param \PatrickLaxton\AnnuaireBundle\Entity\Annuaire $annuaire
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createImportForm($annuaire = null)
    {
        $form = $this->createForm(new AnnuaireType(), $annuaire, array(
            'action' => $this->generateUrl('personne_import_file'),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Import'));

        return $form;
    }
    
    /**
     * @return \PatrickLaxton\AnnuaireBundle\Service\Importer
     */
    private function getImporter() {
        return $this->get('patrick_laxton_annuaire.importer');
    }
    
    /**
     * Imports the file uploaded by the previous action.
     *
     * @Route("/importfile", name="personne_import_file")
     * @Method("PUT")
     * @Template("PatrickLaxtonAnnuaireBundle:Personne:import.file.html.twig")
     */
    public function importFileAction(Request $request) {
        $entity = new \PatrickLaxton\AnnuaireBundle\Entity\Annuaire();

        $importForm = $this->createImportForm($entity);
        $importForm->handleRequest($request);

        if ( $importForm->isValid() ) {
            $dir = Importer::UPLOAD_DIR;
            $filename = $this->getImporter()->getFilename();
            
            $importForm[AnnuaireType::INPUT_NAME]->getData()->move ( $dir,$filename );
            
            $this->getImporter()->import ( "$dir/$filename" );

            return array (
                'importprogress' => $this->generateUrl ( 'personne_import_progress' ),
                'importloaded'   => $this->generateUrl ( 'personne_import_loaded' )
            );
        }

        return $this->redirect ( $this->generateUrl('personne_import') );
    }
    
    /**
     * AJAX action called to know how many lines have been processed (%)
     *
     * @Route("/importprogress", name="personne_import_progress")
     * @Method("GET")
     * @Template("PatrickLaxtonAnnuaireBundle:Personne:import.progress.html.twig")
     */
    public function importProgressAction() {
        return array (
            'progress' => $this->getImporter()->getProgress(),
        );
    }
    
    /**
     * Shows the report after a succesful import
     *
     * @Route("/importloaded", name="personne_import_loaded")
     * @Method("GET")
     * @Template("PatrickLaxtonAnnuaireBundle:Personne:import.loaded.html.twig")
     */
    public function importLoadedAction() {
        return array (
            'report' => $this->getImporter()->getReport(),
        );
    }
    
    /**
     * Finds and displays a Personne entity.
     *
     * @Route("/{id}", name="personne_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PatrickLaxtonAnnuaireBundle:Personne')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Personne entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Edits an existing Personne entity.
     *
     * @Route("/{id}", name="personne_update")
     * @Method("PUT")
     * @Template("PatrickLaxtonAnnuaireBundle:Personne:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PatrickLaxtonAnnuaireBundle:Personne')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Personne entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('personne_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Personne entity.
     *
     * @Route("/{id}", name="personne_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PatrickLaxtonAnnuaireBundle:Personne')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Personne entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('personne'));
    }
    
    /**
     * Displays a form to edit an existing Personne entity.
     *
     * @Route("/{id}/edit", name="personne_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PatrickLaxtonAnnuaireBundle:Personne')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Personne entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
}
