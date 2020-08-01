<?php

namespace App\Controller;

use App\Form\AddUserType;
use App\Form\EditUserType;
use Symfony\Component\Yaml\Yaml;
use App\Form\EditOrganizationType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class YamlAjaxModalController extends AbstractController
{
    /**
     * @Route("/edit-organization/modal/{name_organization}", name="editOrganizationModal", methods={"GET"})
     */
    public function editOrganizationModal($name_organization) // Display the content of the view in a modal
    {
        //Get the organizations from the yaml file 
        $organizations_yaml = Yaml::parseFile('organizations.yaml');

        //We move in he array to find the row that match the parameter in the url
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] === $name_organization) {
                $organization_selected = $organizations_yaml['organizations'][$key];
            }
        }

        /* form edit Organization  */
        //Create the form
        $edit_organization_form = $this->createForm(EditOrganizationType::class, $organization_selected);
        //Render the form
        $render_edit_organization_form = $edit_organization_form->createView();

        return $this->render('yaml_handle/editorganization.html.twig', [
            'controller_name' => 'YamlAjaxModalController',
            'organization' => $organization_selected,
            'editOrganizationForm' => $render_edit_organization_form
        ]);
    }

    /**
     * @Route("/add-user/modal/{name_organization}", name="addUserModal", methods={"GET"})
     */
    public function addUserModal($name_organization) // Display the content of the view in a modal
    {
        //Get the organizations from the yaml file 
        $organizations_yaml = Yaml::parseFile('organizations.yaml');

        //We move in he array to find the row that match the parameter in the url
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] === $name_organization) {
                $organization_selected = $organizations_yaml['organizations'][$key];
            }
        }

        /* form edit Organization  */
        //Create the form
        $add_user_form = $this->createForm(AddUserType::class);
        //Render the form
        $render_add_user_form = $add_user_form->createView();

        return $this->render('yaml_handle/adduser.html.twig', [
            'controller_name' => 'YamlAjaxModalController',
            'organization' => $organization_selected,
            'addUserForm' => $render_add_user_form
        ]);
    }

    /**
     * @Route("/list-user/modal/{name_organization}", name="listUserModal", methods={"GET"})
     */
    public function listUserModal($name_organization) // Display the content of the view in a modal
    {
        //Get the organizations from the yaml file 
        $organizations_yaml = Yaml::parseFile('organizations.yaml');

        //We move in he array to find the row that match the parameter in the url
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] === $name_organization) {
                $organization_selected = $organizations_yaml['organizations'][$key];
            }
        }

        return $this->render('yaml_handle/listusers.html.twig', [
            'controller_name' => 'YamlAjaxModalController',
            'organization' => $organization_selected
        ]);
    }

    /**
     * @Route("/edit-user/modal/{name_organization}/{name_user}", name="editUserModal", methods={"GET"})
     */
    public function editUserModal($name_organization, $name_user) // Display the content of the view in a modal
    {
        //Get the organizations from the yaml file 
        $organizations_yaml = Yaml::parseFile('organizations.yaml');

        //We move in he array to find the row that match the parameter in the url
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if(array_search ($name_organization , $organization , $result_search = true)){
                foreach($organization['users'] as $user_key => $user) {
                    if($user['name'] == $name_user) {
                        $user_selected = $organizations_yaml['organizations'][$key]['users'][$user_key];
                        $organization_selected = $organizations_yaml['organizations'][$key];
                    }
                }
            }
        }

        /* form edit Organization  */
        //Create the form
        $edit_user_form = $this->createForm(EditUserType::class, $user_selected);
        //Render the form
        $render_edit_user_form = $edit_user_form->createView();

        return $this->render('yaml_handle/editusers.html.twig', [
            'controller_name' => 'YamlAjaxModalController',
            'organization' => $organization_selected,
            'user' => $user_selected,
            'editUserForm' => $render_edit_user_form
        ]);
    }
}
