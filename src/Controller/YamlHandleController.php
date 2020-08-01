<?php

namespace App\Controller;

use dump;
use App\Form\AddUserType;
use App\Form\EditUserType;
use Symfony\Component\Yaml\Yaml;
use App\Form\AddOrganizationType;
use App\Form\EditOrganizationType;
use App\Service\YamlFileManagement;
use App\FormValidation\UserValidation;
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
        $organizations_yaml = Yaml::parseFile('organizations.yaml');

        /* form add Organization  */
        //Create the form
        $add_organization_form = $this->createForm(AddOrganizationType::class);
        //Render the form
        $render_add_organization_form = $add_organization_form->createView();

        return $this->render('yaml_handle/index.html.twig', [
            'controller_name' => 'YamlHandleController',
            'organizations' => $organizations_yaml,
            'addOrganizationForm' => $render_add_organization_form
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

        //Validation of the form, validation class are in App\FormValidation
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
     * @Route("/edit-organization/{name_organization}", name="editOrganization", methods={"POST"})
     */
    public function editOrganization($name_organization, Request $request, YamlFileManagement $yaml_file_management, OrganizationValidation $organization_validation)
    {
        //Put the name parameter into a variable
        $name_organization_url_parameter = $name_organization;

        //Handle request of addOrganization form
        $edit_organization_form = $this->createForm(EditOrganizationType::class);
        $edit_organization_form->handleRequest($request);

        //Get the data of the form
        $edit_organization_form_data = $edit_organization_form->getData();

        //To simplify, we put the value in this variable
        $name_organization = $edit_organization_form_data['name_organization'];
        $description_organization = $edit_organization_form_data['description_organization'];

        //Validation of the form, validation class are in App\FormValidation
        $validation = $organization_validation->validation($name_organization, $description_organization);

        //If the form is Submitted and the validation is ok
        if($edit_organization_form->isSubmitted() && $validation === true) {

            //Call function editOrganization who edit the organization in the yaml file.
            // We need to pass the parameter of the url to do the operation
            $yaml_file_management->editOrganization($name_organization_url_parameter, $name_organization, $description_organization);

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
     * @Route("/delete-orga/{name_organization}", name="deleteOrganization")
     */
    public function deleteOrga($name_organization, YamlFileManagement $yaml_file_management)
    {
        //Put the name parameter into a variable
        $name_organization_url_parameter = $name_organization;

        //Call function deleteOrganization who delete the organization in the yaml file.
        $yaml_file_management->deleteOrganization($name_organization_url_parameter);

        return $this->redirectToRoute('Index');
    }

    /**
     * @Route("/add-user-organization/{name_organization}", name="addUser",  methods={"POST"})
     */
    public function addUser($name_organization, Request $request, UserValidation $user_validation, YamlFileManagement $yaml_file_management) {
        
        //Put the name parameter into a variable
        $name_organization_url_parameter = $name_organization;

        // Init the variable that will contain the response
        $response = new Response();

        //Handle request of addOrganization form
        $add_user_form = $this->createForm(AddUserType::class);
        $add_user_form->handleRequest($request);

        //Get the data of the form
        $add_user_form_data = $add_user_form->getData();

        //To simplify, we put the value in this variable
        $name_user = $add_user_form_data['name_user'];
        $roles_user = $add_user_form_data['roles_user'];

        //Validation of the form, validation class are in App\FormValidation
        $validation = $user_validation->validation($name_user, $roles_user);

        //If the form is Submitted and the validation is ok
        if($add_user_form->isSubmitted() && $validation === true) {

            //Call function addUser who add the user of the organization in the yaml file.
            // We need to pass the parameter of the url to do the operation
            $yaml_update_result = $yaml_file_management->addUser($name_organization_url_parameter, $name_user, $roles_user);

            // We decode the result to parse the value to listuser view
            $yaml_update_result_decode = json_decode($yaml_update_result->getContent(), true);

            return $this->render('yaml_ajax_modal/listusers.html.twig', [
                'controller_name' => 'YamlHandleController',
                'organization' => $yaml_update_result_decode['Organization']
            ]);
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
     * @Route("/edit-user-orga/{name_organization}/{name_user}", name="editUser")
     */
    public function editUser($name_organization, $name_user, Request $request, YamlFileManagement $yaml_file_management, UserValidation $user_validation) {
        //Put the name parameter into a variable
        $name_organization_url_parameter = $name_organization;
        $name_user_url_parameter = $name_user;

        // Init the variable that will contain the response
        $response = new Response();

        //Handle request of addOrganization form
        $edit_user_form = $this->createForm(EditUserType::class);
        $edit_user_form->handleRequest($request);

        //Get the data of the form
        $edit_user_form_data = $edit_user_form->getData();

        //To simplify, we put the value in this variable
        $name_user = $edit_user_form_data['name_user'];
        $roles_user = $edit_user_form_data['roles_user'];

        //Validation of the form, validation class are in App\FormValidation
        $validation = $user_validation->validation($name_user, $roles_user);

        //If the form is Submitted and the validation is ok
        if($edit_user_form->isSubmitted() && $validation === true) {

            //Call function editUser who edit the user the organization in the yaml file.
            // We need to pass the parameter of the url to do the operation
            $yaml_update_result = $yaml_file_management->editUser($name_organization_url_parameter, $name_user_url_parameter, $name_user, $roles_user);
            // We decode the result to parse the value to listuser view
            $yaml_update_result_decode = json_decode($yaml_update_result->getContent(), true);

            return $this->render('yaml_ajax_modal/listusers.html.twig', [
                'controller_name' => 'YamlHandleController',
                'organization' => $yaml_update_result_decode['Organization']
            ]);
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
     * @Route("/delete-user-orga/{name_organization}/{name_user}", name="deleteUser")
     */
    public function deleteUser($name_organization, $name_user, Request $request, YamlFileManagement $yaml_file_management) {
        //Put the name parameter into a variable
        $name_organization_url_parameter = $name_organization;
        $name_user_url_parameter = $name_user;

        //Call function deleteUser who delete the user of the organization in the yaml file.
        // We need to pass the parameter of the url to do the operation
        $yaml_update_result = $yaml_file_management->deleteUser($name_organization_url_parameter, $name_user_url_parameter);

        // We decode the result to parse the value to listuser view
        $yaml_update_result_decode = json_decode($yaml_update_result->getContent(), true);

        return $this->render('yaml_ajax_modal/listusers.html.twig', [
            'controller_name' => 'YamlHandleController',
            'organization' => $yaml_update_result_decode['Organization']
        ]);
    }

}
