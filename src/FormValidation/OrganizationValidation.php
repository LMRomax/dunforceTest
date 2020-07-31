<?php 

    namespace App\FormValidation;

    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Validation;

    class OrganizationValidation 
    {
        public function validation($name_organization, $description_organization) {

            $validator = Validation::createValidator();

            $data = ['name_organization' => $name_organization, 'description_organization' => $description_organization];
    
            $constraints = new Assert\Collection([
                'name_organization' => [new Assert\Type('string'), new Assert\NotBlank],
                'description_organization' => [new Assert\Type('string'), new Assert\notBlank, new Assert\Length(['max' => 512])],
            ]);
    
            $violations = $validator->validate($data, $constraints);

            if(count($violations) > 0) {
                return $violations;
            }
            else {
                return true;
            }
        }
    }
?>