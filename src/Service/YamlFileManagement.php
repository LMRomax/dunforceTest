<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class YamlFileManagement
{
    // Function to add an Organization in organizations.yaml
    public function addOrganization($name, $description) {
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        $new_organization_yaml = ['name' => $name, 'description' => $description, 'users' => [] ];

        array_push($organizations_yaml["organizations"], $new_organization_yaml);

        $update_yaml = Yaml::dump($organizations_yaml);    

        file_put_contents('organizations.yaml', $update_yaml);
    }

    // Function to edit an Organization in organizations.yaml
    public function editOrganization($name_url_parameter, $name, $description) {
        $organizations_yaml = Yaml::parseFile('organizations.yaml'); 

        foreach($organizations_yaml['organizations'] as $key => $organization) {
            if($organization['name'] == $name_url_parameter) {
                $organizations_yaml['organizations'][$key]['name'] = $name;
                $organizations_yaml['organizations'][$key]['description'] = $description;
            }
        }

        $update_yaml = Yaml::dump($organizations_yaml);    

        file_put_contents('organizations.yaml', $update_yaml);
    }

    // Function to remove an Organization in organizations.yaml
    public function deleteOrganization() {
        //
    }

    // Function to add a user in a organization in organizations.yaml
    public function addUser() {
        //
    }

    // Function to edit a user in a organization in organizations.yaml
    public function editUser() {
        //
    }

    // Function to remove a user in a organization in organizations.yaml
    public function deleteUser() {
        //
    }
}

?>