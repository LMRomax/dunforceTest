<?php

namespace App\Controller;

use dump;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class YamlHandleController extends AbstractController
{
    /**
     * @Route("/", name="Index")
     */
    public function index()
    {
        $organizations = Yaml::parseFile('organizations.yaml');

        return $this->render('yaml_handle/index.html.twig', [
            'controller_name' => 'YamlHandleController',
            'organizations' => $organizations,
        ]);
    }

    /**
     * @Route("/add-orga", name="addOrga")
     */
    public function addOrga(Request $request, ValidatorInterface $validator)
    {
        $token = $request->request->get("token");

        if($this->isCsrfTokenValid('addOrga', $token)) {

            $name = $request->request->get("name_orga");
            $description = $request->request->get("description_orga");

            $input = ['name_orga' => $name, 'description_orga' => $description];

            $constraints = new Assert\Collection([
                'name_orga' => [new Assert\Type('string'), new Assert\NotBlank],
                'description_orga' => [new Assert\Type('string'), new Assert\notBlank, new Assert\Length(['max' => 512])],
            ]);

            $violations = $validator->validate($input, $constraints);

            if (count($violations) > 0) { 

                $errorMessages = array();

                foreach ($violations as $violation) {
                    $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
                }

                /*dump($errorMessages);
                die();*/

                $this->addFlash(
                    'Error',
                    $errorMessages
                );

                return $this->redirectToRoute('Index');
            }
            else {

                $organizations = Yaml::parseFile('organizations.yaml'); 

                $new_organization = ['name' => $name, 'description' => $description, 'users' => [] ];

                array_push($organizations["organizations"], $new_organization);

                $yaml = Yaml::dump($organizations);    

                file_put_contents('organizations.yaml', $yaml);

                return $this->redirectToRoute('Index');
            }

            return $this->redirectToRoute('Index');
        }
        else {
            $this->addFlash(
                'Error',
                'A problem has occured with the CSRF Token'
            );

            return $this->redirectToRoute('Index');
        }

    }

    /**
     * @Route("/edit-orga/{name}", name="editOrga")
     */
    public function editOrga($name, Request $request, ValidatorInterface $validator)
    {
        $token = $request->request->get("token");

        if($this->isCsrfTokenValid('editOrga', $token)) {

            $organizations = Yaml::parseFile('organizations.yaml'); 

            $name = $request->request->get("editname_orga");
            $description = $request->request->get("editdescription_orga");
    
            $input = ['editname_orga' => $name, 'editdescription_orga' => $description];
    
            $constraints = new Assert\Collection([
                'editname_orga' => [new Assert\Type('string'), new Assert\NotBlank],
                'editdescription_orga' => [new Assert\Type('string'), new Assert\notBlank, new Assert\Length(['max' => 512])],
            ]);
    
            $violations = $validator->validate($input, $constraints);

            if (count($violations) > 0) { 
                $errorMessages = array();

                foreach ($violations as $violation) {
                    $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
                }

                /*dump($errorMessages);
                die();*/

                $this->addFlash(
                    'Error',
                    $errorMessages
                );

                return $this->redirectToRoute('Index');
            }
            else {

                foreach($organizations['organizations'] as $key => $organization) {
                    if($organization['name'] == $name) {
                        $organizations['organizations'][$key]['name'] = $input['editname_orga'];
                        $organizations['organizations'][$key]['description'] = $input['editdescription_orga'];
                    }
                }
        
                $yaml = Yaml::dump($organizations);    
        
                file_put_contents('organizations.yaml', $yaml);
        
                return $this->redirectToRoute('Index');
            }
        }
        else {
            $this->addFlash(
                'Error',
                'A problem has occured with the CSRF Token'
            );

            return $this->redirectToRoute('Index');
        }

    }

    /**
     * @Route("/delete-orga/{name}", name="deleteOrga")
     */
    public function deleteOrga($name, Request $request, ValidatorInterface $validator)
    {
        $organizations = Yaml::parseFile('organizations.yaml'); 

        foreach($organizations['organizations'] as $key => $organization) {
            if($organization['name'] == $name) {
                unset($organizations['organizations'][$key]);
            }
        }

        $yaml = Yaml::dump($organizations);    

        file_put_contents('organizations.yaml', $yaml);

        return $this->redirectToRoute('Index');
    }

    /**
     * @Route("/add-user-orga/{name_orga}", name="addUser")
     */
    public function addUser($name_orga, Request $request, ValidatorInterface $validator) {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $token = $request->request->get("token");

            if($this->isCsrfTokenValid('addUser', $token)) {
                $organizations = Yaml::parseFile('organizations.yaml'); 

                $name = $request->request->get("name_userorga");
                $roles = $request->request->get("role_userorga");
    
                $input = ['name_userorga' => $name, 'role_userorga' => $roles];
        
                $constraints = new Assert\Collection([
                    'name_userorga' => [new Assert\Type('string'), new Assert\NotBlank],
                    'role_userorga' => [new Assert\Type('string'), new Assert\notBlank],
                ]);
    
                $violations = $validator->validate($input, $constraints);

                if (count($violations) > 0) { 
                    $errorMessages = array();

                    foreach ($violations as $violation) {
                        $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
                    }
    
                    /*dump($errorMessages);
                    die();*/
    
                    return new JsonResponse([
                        'errors' => $errorMessages
                    ], 500);
                }
                else {
                    $array_roles = explode(',', $roles);

                    foreach($organizations['organizations'] as $key => $organization) {
                        if($organization['name'] == $name_orga) {
                            $new_user =  [
                                'name' => $name, 
                                'role' => array_values($array_roles),
                                'password' => bin2hex(random_bytes(10))
                            ];

                            array_push($organizations['organizations'][$key]['users'], $new_user);
                        }
                    }

                    $yaml = Yaml::dump($organizations);    
        
                    file_put_contents('organizations.yaml', $yaml);

                    return new JsonResponse([
                        'Organizations' => $organizations
                    ], 200);
                }
            }
            else {
                return new JsonResponse([
                    'error' => 'An error occured with the CSRF Token'
                ], 500);
            }
        }
        else {
            return new JsonResponse([
                'error' => 'An error occured'
            ], 500);
        }
    }

        /**
     * @Route("/edit-user-orga/{name_orga}/{user_name}", name="editUser")
     */
    public function editUser($name_orga, $user_name, Request $request, ValidatorInterface $validator) {
        $response = new Response();
        
        if($request->isXmlHttpRequest()){

            $organizations = Yaml::parseFile('organizations.yaml'); 

            $name = $request->request->get("editname_userorga");
            $roles = $request->request->get("editrole_userorga");

            $input = ['name_userorga' => $name, 'role_userorga' => $roles];
    
            $constraints = new Assert\Collection([
                'name_userorga' => [new Assert\Type('string'), new Assert\NotBlank],
                'role_userorga' => [new Assert\Type('string'), new Assert\notBlank],
            ]);

            $violations = $validator->validate($input, $constraints);

            if (count($violations) > 0) { 
                $errorMessages = array();

                foreach ($violations as $violation) {
                    $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
                }

                /*dump($errorMessages);
                die();*/

                return new JsonResponse([
                    'errors' => $errorMessages
                ], 500);
            }
            else {
                $array_roles = explode(',', $roles);

                foreach($organizations['organizations'] as $key => $organization) {
                    if(array_search ($name_orga , $organization , $result_search = true)){
                        foreach($organization['users'] as $user_key => $user) {
                            if($user['name'] == $user_name) {
                                $organizations['organizations'][$key]['users'][$user_key]['name'] = $input['name_userorga'];
                                $organizations['organizations'][$key]['users'][$user_key]['role'] = array_values($array_roles);
                                $user_orga = $organizations['organizations'][$key]['users'][$user_key];
                            }
                        }
                    }
                    /*else {
                        return new JsonResponse([
                            'error' => array_search ($name_orga , $organization , $result_search = true)
                        ], 500);
                    }*/
                }

                $yaml = Yaml::dump($organizations);    
    
                file_put_contents('organizations.yaml', $yaml);

                return new JsonResponse([
                    'Organizations' => $user_orga,
                ], 200);
            }
          
        }
        else {
            return new JsonResponse([
                'error' => 'An error occured'
            ], 500);   
        }
    }

    /**
     * @Route("/delete-user-orga/{name_orga}/{user_name}", name="deleteUser")
     */
    public function deleteUser($name_orga, $user_name, Request $request, ValidatorInterface $validator) {
        if($request->isXmlHttpRequest()){
            $organizations = Yaml::parseFile('organizations.yaml'); 

            foreach($organizations['organizations'] as $key => $organization) {
                if($organization['name'] == $name_orga) {
                    foreach($organization['users'] as $user_key => $user) {
                        if($user['name'] == $user_name) {
                            unset($organizations['organizations'][$key]['users'][$user_key]);
                        }
                    }
                }
            }

            $yaml = Yaml::dump($organizations);    
        
            file_put_contents('organizations.yaml', $yaml);

            return new JsonResponse([
                'success' => 'User deleted'
            ], 200); 
        }
        else {
            return new JsonResponse([
                'error' => 'An error occured'
            ], 500);     
        }
    }

}
