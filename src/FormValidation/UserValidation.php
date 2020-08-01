<?php 

    namespace App\FormValidation;

    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Validation;

    class UserValidation 
    {
        public function validation($name_user, $roles_user) {

            $validator = Validation::createValidator();

            $data = ['name_user' => $name_user, 'roles_user' => $roles_user];
    
            $constraints = new Assert\Collection([
                'name_user' => [new Assert\Type('string'), new Assert\NotBlank],
                'roles_user' => [new Assert\Type('string'), new Assert\notBlank, new Assert\Length(['max' => 512])],
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