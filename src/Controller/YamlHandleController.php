<?php

namespace App\Controller;

use dump;
use Symfony\Component\Yaml\Yaml;
use App\Form\AddOrganizationType;
use App\Form\EditOrganizationType;
use App\Service\YamlFileManagement;
use Symfony\Component\HttpFoundation\Request;
use App\FormValidation\OrganizationValidation;
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
        //Get the organizations from the yaml file 
        $organizations = Yaml::parseFile('organizations.yaml');

        /* form add Organization  */
        //Create the form
        $add_organization_form = $this->createForm(AddOrganizationType::class);
        //Render the form
        $render_add_organization_form = $add_organization_form->createView();

        /* form edit Organization  */
        //Create the form
        $edit_organization_form = $this->createForm(EditOrganizationType::class, $organizations);
        //Render the form
        $render_edit_organization_form = $edit_organization_form->createView();

        return $this->render('yaml_handle/index.html.twig', [
            'controller_name' => 'YamlHandleController',
            'organizations' => $organizations,
            'addOrganizationForm' => $render_add_organization_form,
            'editOrganizationForm' => $render_edit_organization_form
        ]);
    }

    /**
     * @Route("/edit-organization/modal/{name}", name="editOrganizationModal", methods={"GET"})
     */
    public function editOrganizationModal($name) // Display the content of the view in a modal
    {
        //Get the organizations from the yaml file 
        $organizations = Yaml::parseFile('organizations.yaml');

        //We move in he array to find the row that match the parameter in the url
        foreach($organizations['organizations'] as $key => $organization) {
            if($organization['name'] === $name) {
                $organization_selected = $organizations['organizations'][$key];
            }
        }

        /* form edit Organization  */
        //Create the form
        $edit_organization_form = $this->createForm(EditOrganizationType::class, $organization_selected);
        //Render the form
        $render_edit_organization_form = $edit_organization_form->createView();

        return $this->render('yaml_handle/edit.html.twig', [
            'controller_name' => 'YamlHandleController',
            'organization' => $organization_selected,
            'editOrganizationForm' => $render_edit_organization_form
        ]);
    }

    /**
     * @Route("/add-organization", name="addOrganization", methods={"POST"})
     */
    public function addOrganization(Request $request, YamlFileManagement $yaml_file_management, OrganizationValidation $organization_validation)
    {
        //Handle request of addOrganization form
        $add_organization_form = $this->createForm(AddOrganizationType::class);
        $add_organization_form->handleRequest($request);

        //Get the data of the form
        $add_organization_form_data = $add_organization_form->getData();

        //To simplify, we put the value in this variable
        $name_organization = $add_organization_form_data['name_organization'];
        $description_organization = $add_organization_form_data['description_organization'];

        //Validation of the form
        $validation = $organization_validation->validation($name_organization, $description_organization);

        //If the form is Submitted and the validation is ok
        if($add_organization_form->isSubmitted() && $validation === true) {

            //Call function addOrganization who add the organization in the yaml file.
            $yaml_file_management->addOrganization($name_organization, $description_organization);

            return $this->redirectToRoute('Index');
        } // If validation === false, we create an array of error and create a flash message for the user
        else {
            // Init the array
            $errorMessages = array();

            //Put the messages in the array
            foreach ($validation as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            //Create the Flash message
            $this->addFlash(
                'Error',
                $errorMessages
            );

            return $this->redirectToRoute('Index');
        }
    }

    /**
     * @Route("/edit-organization/{name}", name="editOrganization", methods={"POST"})
     */
    public function editOrganization($name, Request $request, YamlFileManagement $yaml_file_management, OrganizationValidation $organization_validation)
    {
        //Put the name parameter into a variable
        $name_url_parameter = $name;

        //Handle request of addOrganization form
        $edit_organization_form = $this->createForm(EditOrganizationType::class);
        $edit_organization_form->handleRequest($request);

        //Get the data of the form
        $edit_organization_form_data = $edit_organization_form->getData();

        //To simplify, we put the value in this variable
        $name_organization = $edit_organization_form_data['name_organization'];
        $description_organization = $edit_organization_form_data['description_organization'];

        //Validation of the form
        $validation = $organization_validation->validation($name_organization, $description_organization);

        //If the form is Submitted and the validation is ok
        if($edit_organization_form->isSubmitted() && $validation === true) {

            //Call function editOrganization who add the organization in the yaml file.
            // We need to pass the parameter of the url to do the operation
            $yaml_file_management->editOrganization($name_url_parameter, $name_organization, $description_organization);

            return $this->redirectToRoute('Index');
        } // If validation === false, we create an array of error and create a flash message for the user
        else {
            // Init the array
            $errorMessages = array();

            //Put the messages in the array
            foreach ($validation as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            //Create the Flash message
            $this->addFlash(
                'Error',
                $errorMessages
            );

            return $this->redirectToRoute('Index');
        }
    }

    /**
     * @Route("/delete-orga/{name}", name="deleteOrganization")
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
