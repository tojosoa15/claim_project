<?php
namespace App\DTO;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class GetClaimsByUserInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;
    
    // #[ApiProperty(default: 'false')]
    // public ?string $received_date = 'false';
    
    // public ?string $number = null;
    // public ?string $name = null;
    // public ?string $registration_number = null;
    
    // #[ApiProperty(default: 'false')]
    // public ?string $agein = 'false';
    
    // public ?string $phone = null;
    // public ?string $status = null;
}