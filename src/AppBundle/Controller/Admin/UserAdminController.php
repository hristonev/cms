<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 30.08.17
 * Time: 11:37
 */

namespace AppBundle\Controller\Admin;


use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserAdminController extends Controller
{
    private function getSystemRoles()
    {
        $security = $this->get('security.authorization_checker');
        $roleCollection = array_unique(array_keys($this->getParameter('security.role_hierarchy.roles')));
        $roleAllow = [];
        foreach ($roleCollection as $role){
            if($security->isGranted($role)){
                $roleAllow[] = $role;
            }
        }
        return $roleAllow;
    }

    private function checkUserAllowEdit(User $user)
    {
        $security = $this->get('security.authorization_checker');
        foreach ($user->getRoles() as $role){
            if(!$security->isGranted($role)){
                return false;
            }
        }

        return true;
    }

    /**
     * @Route("/admin/user/list", name="admin_user_list")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction()
    {
        $roleCollection = $this->getSystemRoles();

        /** @var User[] $user */
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findAll();
        return $this->render('admin/user_list.html.twig', [
            'userCollection' => $user,
            'roles' => $roleCollection
        ]);
    }

    /**
     * @Route("/admin/xhr/user_edit", schemes={"http"}, name="xhr-admin-user-edit", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function editUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new \stdClass();

        $data->id = $request->request->get('id');
        $data->error = false;
        $data->errorMsg = '';
        $data->roles = [];

        $user = $em->getRepository('AppBundle:User')->find($data->id);
        if(!$this->checkUserAllowEdit($user)){
            $data->error = true;
            $data->errorMsg = 'Not allowed!';
            return new JsonResponse($data);
        }

        $sysRoles = $this->getSystemRoles();
        $userRoles = $user->getRoles();
        foreach ($sysRoles as $role){
            $item = & $data->roles[];
            $item = new \stdClass();
            $item->role = $role;
            if(array_search($role, $userRoles) !== false){
                $item->allow = true;
            }else{
                $item->allow = false;
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/xhr/user_save", schemes={"http"}, name="xhr-admin-user-save", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = new \stdClass();
        $data->error = false;
        $data->errorMsg = '';

        $user = $em->getRepository('AppBundle:User')->find($request->request->get('id'));
        if(!$this->checkUserAllowEdit($user)){
            $data->error = true;
            $data->errorMsg = 'Not allowed!';
            return new JsonResponse($data);
        }

        $data->roles = $request->request->get('role');
        $user->setRoles($data->roles);
        $em->persist($user);
        $em->flush();

        return new JsonResponse($data);
    }

    /**
     * @Route("/admin/xhr/user_delete", schemes={"http"}, name="xhr-admin-user-delete", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = new \stdClass();
        $data->error = false;
        $data->errorMsg = '';

        $user = $em->getRepository('AppBundle:User')->find($request->request->get('id'));
        if($user && $this->checkUserAllowEdit($user)){
            $em->remove($user);
            $em->flush();
        }else{
            $data->error = true;
            $data->errorMsg = 'Not allowed!';
        }

        return new JsonResponse($data);
    }
}