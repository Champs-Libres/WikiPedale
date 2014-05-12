<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Progracqteur\WikipedaleBundle\Entity\Model\Category;
use Progracqteur\WikipedaleBundle\Form\Model\CategoryType;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller
{
    /**
     * 
     * Lists all the Categories in a JSON array, respecting the Parent/Children specification :
     * - first all the parents are given,
     * - in every parents the variable "children" give the list of all its children
     */
    public function listParentChildrenAction()
    {
        $em = $this->getDoctrine()->getManager();

        $terms_allowed = ' ';
        $terms_allowed_array = array();
        $iTerm = 0;
        foreach ($this->get('service_container')->getParameter('report_types') 
                as $target => $array) {
            //TODO extendds to other transports
            if ($target === 'bike') {
                foreach ($array["terms"] as $term) {
                    if ($this->get('security.context')->isGranted(
                            $term['mayAddToReport'])){
                        if ($iTerm > 0) {
                            $terms_allowed .= ', ';
                        }
                        $terms_allowed .= "'".$term['key']."'";
                        $terms_allowed_array[] = $term['key'];
                        $iTerm ++;
                    }   
                }
            }
        }
        
        $terms_allowed .= ' ';

        $q = sprintf('SELECT c from 
            ProgracqteurWikipedaleBundle:Model\Category c 
            WHERE  c.used = true AND c.parent is null AND c.term IN (%s)
            ORDER BY c.order, c.label', $terms_allowed);
        $categories = $em->createQuery($q)->getResult();

        $rep = new NormalizedResponse($categories);
        $ret = $this->get('progracqteurWikipedaleSerializer')->serialize($rep, 'json');
        return new Response($ret);
    }

    /**
     * Lists all Category entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Category')->findAll();

        return $this->render('ProgracqteurWikipedaleBundle:Model/Category:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Category entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Model\Category entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ProgracqteurWikipedaleBundle:Model/Category:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Category entity.
     *
     */
    public function newAction()
    {
        $entity = new Category();
        $form   = $this->createForm(
                new CategoryType($this->getDoctrine()->getManager()), $entity);

        return $this->render('ProgracqteurWikipedaleBundle:Model/Category:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Category entity.
     *
     */
    public function createAction()
    {
        $entity  = new Category();
        $request = $this->getRequest();
        $form    = $this->createForm(
                new CategoryType($this->getDoctrine()->getManager()), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_category_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ProgracqteurWikipedaleBundle:Model/Category:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Category entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Model\Category entity.');
        }

        $editForm = $this->createForm(new CategoryType(
                $this->getDoctrine()->getManager()), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ProgracqteurWikipedaleBundle:Model/Category:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Category entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Model\Category entity.');
        }

        $editForm   = $this->createForm(new CategoryType(
                $this->getDoctrine()->getManager()), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_category_edit', array('id' => $id)));
        }

        return $this->render('ProgracqteurWikipedaleBundle:Model/Category:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Category entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Category')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Model\Category entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_category'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
