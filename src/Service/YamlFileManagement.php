<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class YamlFileManagement
{
    // Function to add an Organization in organizations.yaml
    public function addOrganization($name, $description) {
        //Put the yaml file content in a variable
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        //Create an array with the data to push in the array $organizations_yaml
        $new_organization_yaml = ['name' => $name, 'description' => $description, 'users' => [] ];

        //We push the $new_organization_yaml array at the end of the array $organizations_yaml
        array_push($organizations_yaml["organizations"], $new_organization_yaml);

        // Create the $organizations_yaml array in yaml representation
        $update_yaml = Yaml::dump($organizations_yaml);    

        // Write the new array in the file
        file_put_contents('organizations.yaml', $update_yaml);
    }

    // Function to edit an Organization in organizations.yaml
    public function editOrganization($name_organization_url_parameter, $name, $description) {
        //Put the yaml file content in a variable
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        //We move in the array to find the row with the same name than the parameter
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] == $name_organization_url_parameter) {
                $organizations_yaml['organizations'][$key]['name'] = $name;
                $organizations_yaml['organizations'][$key]['description'] = $description;
            }
        }

        // Create the $organizations_yaml array in yaml representation
        $update_yaml = Yaml::dump($organizations_yaml);    

        // Write the new array in the file
        file_put_contents('organizations.yaml', $update_yaml);
    }

    // Function to remove an Organization in organizations.yaml
    public function deleteOrganization($name_organization_url_parameter) {
        //Put the yaml file content in a variable
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        //We move in the array to find the row with the same name than the parameter
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] == $name_organization_url_parameter) {
                unset($organizations_yaml['organizations'][$key]);
            }
        }

        // Create the $organizations_yaml array in yaml representation
        $update_yaml = Yaml::dump($organizations_yaml);    

        // Write the new array in the file
        file_put_contents('organizations.yaml', $update_yaml);
    }

    // Function to add a user in a organization in organizations.yaml
    public function addUser($name_organization_url_parameter, $name_user, $roles_user) {
        //Put the yaml file content in a variable
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        //Put the string of role into an array
        $array_roles = explode(',', $roles_user);

        //We move in the array to find the row with the same organization name than the parameter
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] == $name_organization_url_parameter) {
                $new_user =  [
                    'name' => $name_user, 
                    'role' => array_values($array_roles),
                    'password' => bin2hex(random_bytes(10))
                ];

                array_push($organizations_yaml['organizations'][$key]['users'], $new_user);
                $organization_selected = $organizations_yaml['organizations'][$key];
            }
        }

        // Create the $organizations_yaml array in yaml representation
        $update_yaml = Yaml::dump($organizations_yaml);    

        // Write the new array in the file
        file_put_contents('organizations.yaml', $update_yaml);

        //Return a json response with the needed data
        return new JsonResponse([
            'Organization' => $organization_selected
        ], 200);
    }

    // Function to edit a user in a organization in organizations.yaml
    public function editUser($name_organization_url_parameter, $name_user_url_parameter, $name_user, $roles_user) {
        //Put the yaml file content in a variable
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        //Put the string of role into an array
        $array_roles = explode(',', $roles_user);

        //We move in the array to find the row with the same organization name and user name than the parameter
        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if(array_search($name_organization_url_parameter , $organization , $result_search = true)){
                foreach($organization['users'] as $user_key => $user) {
                    if($user['name'] == $name_user_url_parameter) {
                        $organizations_yaml['organizations'][$key]['users'][$user_key]['name'] = $name_user;
                        $organizations_yaml['organizations'][$key]['users'][$user_key]['role'] = array_values($array_roles);
                        $user_selected = $organizations_yaml['organizations'][$key]['users'][$user_key];
                        $organization_selected = $organizations_yaml['organizations'][$key];
                    }
                }
            }
        }

        // Create the $organizations_yaml array in yaml representation
        $update_yaml = Yaml::dump($organizations_yaml);    

        // Return a json response with a code status
        file_put_contents('organizations.yaml', $update_yaml);

        //Return a json response with the needed data
        return new JsonResponse([
            'Organization' => $organization_selected,
            'User' => $user_selected
        ], 200);
    }

    // Function to remove a user in a organization in organizations.yaml
    public function deleteUser($name_organization_url_parameter, $name_user_url_parameter) {
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] == $name_organization_url_parameter) {
                foreach($organization['users'] as $user_key => $user) {
                    if($user['name'] == $name_user_url_parameter) {
                        unset($organizations_yaml['organizations'][$key]['users'][$user_key]);
                        $organization_selected = $organizations_yaml['organizations'][$key];
                    }
                }
            }
        }

        // Create the $organizations_yaml array in yaml representation
        $update_yaml = Yaml::dump($organizations_yaml);    
    
        // Return a json response with a code status
        file_put_contents('organizations.yaml', $update_yaml);

        //Return a json response with the needed data
        return new JsonResponse([
            'Organization' => $organization_selected,
        ], 200);
    }
}

?>